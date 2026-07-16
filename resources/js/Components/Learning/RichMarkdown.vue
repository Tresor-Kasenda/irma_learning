<script lang="ts" setup>
import renderMathInElement from 'katex/contrib/auto-render';
import 'katex/dist/katex.min.css';
import 'prismjs/themes/prism-tomorrow.css';
import {nextTick, onMounted, ref, watch} from 'vue';

const props = defineProps<{html: string}>();
const container = ref<HTMLElement | null>(null);
let renderVersion = 0;
type PrismApi = typeof import('prismjs');
let prismLoader: Promise<PrismApi> | null = null;

async function copyCode(code: string): Promise<void> {
    if (navigator.clipboard?.writeText) {
        try {
            await navigator.clipboard.writeText(code);

            return;
        } catch {
            // The fallback below also supports browsers that deny clipboard access.
        }
    }

    const textarea = document.createElement('textarea');
    textarea.value = code;
    textarea.setAttribute('readonly', '');
    textarea.className = 'sr-only';
    document.body.append(textarea);
    textarea.select();

    const copied = document.execCommand('copy');
    textarea.remove();

    if (! copied) {
        throw new Error('Impossible de copier le code.');
    }
}

function addCopyButtons(element: HTMLElement): void {
    for (const code of element.querySelectorAll<HTMLElement>('pre > code:not(.language-mermaid)')) {
        const pre = code.parentElement;
        if (! pre || pre.parentElement?.classList.contains('markdown-code-frame')) {
            continue;
        }

        pre.classList.add('markdown-code-block');
        const frame = document.createElement('div');
        frame.className = 'markdown-code-frame';
        pre.replaceWith(frame);
        frame.append(pre);

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'markdown-code-copy';
        button.textContent = 'Copier';
        button.setAttribute('aria-label', 'Copier le code');
        button.addEventListener('click', async () => {
            try {
                await copyCode(code.textContent ?? '');
                button.textContent = 'Copié';
            } catch {
                button.textContent = 'Indisponible';
            }

            window.setTimeout(() => {
                button.textContent = 'Copier';
            }, 1800);
        });

        frame.append(button);
    }
}

function applyMermaidTheme(wrapper: HTMLElement, isDark: boolean): void {
    const svg = wrapper.querySelector<SVGElement>('svg');
    if (! svg) {
        return;
    }

    const colors = isDark
        ? {
            background: '#101d2d',
            node: '#1b365d',
            border: '#60a5fa',
            text: '#f8fafc',
            edge: '#94a3b8',
            edgeLabel: '#101d2d',
        }
        : {
            background: '#ffffff',
            node: '#eff6ff',
            border: '#2563eb',
            text: '#172033',
            edge: '#475569',
            edgeLabel: '#ffffff',
        };

    wrapper.classList.toggle('markdown-mermaid-dark', isDark);
    svg.style.backgroundColor = colors.background;
    svg.style.color = colors.text;

    for (const shape of svg.querySelectorAll<SVGElement>('.node rect, .node polygon, .node circle, .node ellipse, .cluster rect')) {
        shape.setAttribute('fill', colors.node);
        shape.setAttribute('stroke', colors.border);
    }

    for (const label of svg.querySelectorAll<SVGElement>('text, tspan, .label, .nodeLabel, .edgeLabel')) {
        label.setAttribute('fill', colors.text);
        label.style.setProperty('color', colors.text, 'important');
        label.style.setProperty('fill', colors.text, 'important');
    }

    for (const label of svg.querySelectorAll<HTMLElement>('foreignObject, foreignObject *')) {
        label.style.setProperty('color', colors.text, 'important');
        label.style.setProperty('fill', colors.text, 'important');
    }

    for (const edge of svg.querySelectorAll<SVGElement>('.flowchart-link, .edgePath path, .edge-thickness-normal')) {
        edge.setAttribute('stroke', colors.edge);
    }

    for (const edgeLabel of svg.querySelectorAll<SVGElement>('.edgeLabel rect')) {
        edgeLabel.setAttribute('fill', colors.edgeLabel);
        edgeLabel.setAttribute('stroke', 'none');
    }
}

function hasDarkLearningTheme(element: HTMLElement): boolean {
    if (document.documentElement.classList.contains('dark')) {
        return true;
    }

    const background = getComputedStyle(element).backgroundColor;
    const components = background.match(/\d+(?:\.\d+)?/g)?.map(Number);

    if (! components || components.length < 3) {
        return false;
    }

    return (components[0] * 0.299) + (components[1] * 0.587) + (components[2] * 0.114) < 128;
}

function loadPrism(): Promise<PrismApi> {
    if (! prismLoader) {
        const prismGlobal = globalThis as unknown as {Prism?: {manual?: boolean}};
        prismGlobal.Prism = {manual: true};

        prismLoader = import('prismjs')
            .then(async (prismModule) => {
                const prism = ((prismModule as unknown as {default?: PrismApi}).default ?? prismModule) as PrismApi;

                // Prism components are side-effect modules that read the global
                // instance instead of the value returned by the ESM import.
                (globalThis as unknown as {Prism: PrismApi}).Prism = prism;

                await import('prismjs/components/prism-markup-templating');
                await Promise.all([
                    import('prismjs/components/prism-bash'),
                    import('prismjs/components/prism-json'),
                    import('prismjs/components/prism-php'),
                    import('prismjs/components/prism-python'),
                    import('prismjs/components/prism-sql'),
                    import('prismjs/components/prism-typescript'),
                ]);

                return prism;
            })
            .catch((error) => {
                prismLoader = null;
                throw error;
            });
    }

    return prismLoader;
}

async function enhanceContent(): Promise<void> {
    const version = ++renderVersion;
    await nextTick();

    const element = container.value;
    if (! element || version !== renderVersion) {
        return;
    }

    renderMathInElement(element, {
        delimiters: [
            {left: '$$', right: '$$', display: true},
            {left: '\\[', right: '\\]', display: true},
            {left: '\\(', right: '\\)', display: false},
        ],
        throwOnError: false,
    });

    const mermaidBlocks = Array.from(element.querySelectorAll<HTMLElement>('pre code.language-mermaid'));
    if (mermaidBlocks.length > 0) {
        const {default: mermaid} = await import('mermaid');
        const isDark = hasDarkLearningTheme(element);
        mermaid.initialize({
            startOnLoad: false,
            securityLevel: 'antiscript',
            theme: 'base',
            flowchart: {
                htmlLabels: true,
                useMaxWidth: true,
            },
            themeVariables: isDark ? {
                background: '#101d2d',
                primaryColor: '#1b365d',
                primaryTextColor: '#f8fafc',
                primaryBorderColor: '#60a5fa',
                lineColor: '#94a3b8',
                secondaryColor: '#243b62',
                tertiaryColor: '#0f1d2d',
                clusterBkg: '#101d2d',
                clusterBorder: '#475569',
                edgeLabelBackground: '#101d2d',
                fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
            } : {
                background: '#ffffff',
                primaryColor: '#eff6ff',
                primaryTextColor: '#172033',
                primaryBorderColor: '#2563eb',
                lineColor: '#475569',
                secondaryColor: '#f8fafc',
                tertiaryColor: '#f1f5f9',
                clusterBkg: '#f8fafc',
                clusterBorder: '#94a3b8',
                edgeLabelBackground: '#ffffff',
                fontFamily: 'Inter, ui-sans-serif, system-ui, sans-serif',
            },
        });

        for (const block of mermaidBlocks) {
            const source = block.textContent?.trim() ?? '';
            const wrapper = document.createElement('div');
            wrapper.className = 'markdown-mermaid';
            block.parentElement?.replaceWith(wrapper);

            try {
                await mermaid.parse(source);
                const {svg} = await mermaid.render(`learning-mermaid-${version}-${Math.random().toString(36).slice(2)}`, source);
                wrapper.innerHTML = svg;
                applyMermaidTheme(wrapper, isDark);
            } catch {
                wrapper.classList.add('markdown-mermaid-error');
                wrapper.textContent = 'Ce diagramme ne peut pas être affiché. Vérifiez sa syntaxe Mermaid.';
            }
        }
    }

    try {
        const prism = await loadPrism();
        if (version === renderVersion) {
            prism.highlightAllUnder(element);
            addCopyButtons(element);
        }
    } catch (error) {
        console.warn('La coloration syntaxique du contenu Markdown a échoué.', error);
    }
}

watch(() => props.html, () => void enhanceContent());
onMounted(() => void enhanceContent());
</script>

<template>
    <div ref="container" class="rich-markdown" v-html="html"/>
</template>
