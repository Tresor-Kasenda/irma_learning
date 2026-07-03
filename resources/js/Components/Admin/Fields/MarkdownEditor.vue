<script lang="ts" setup>
import {
    Bold,
    Braces,
    Code2,
    Eye,
    Heading1,
    Image,
    Italic,
    Link2,
    List,
    ListOrdered,
    Quote,
    Table2,
    Workflow,
} from '@lucide/vue';
import DOMPurify from 'dompurify';
import katexExtension from 'marked-katex-extension';
import {Marked} from 'marked';
import {computed, nextTick, onBeforeUnmount, onMounted, ref, watch} from 'vue';
import 'katex/dist/katex.min.css';
import FieldWrapper from '@/Components/Admin/Fields/FieldWrapper.vue';

const props = withDefaults(defineProps<{
    modelValue: string;
    label?: string;
    id?: string;
    error?: string;
    hint?: string;
    required?: boolean;
    placeholder?: string;
}>(), {
    label: 'Contenu Markdown',
    id: 'markdown-editor',
    placeholder: '# Commencez à rédiger…',
});

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const markdown = new Marked({
    breaks: true,
    gfm: true,
});

markdown.use(katexExtension({
    nonStandard: true,
    throwOnError: false,
}));

const textareaRef = ref<HTMLTextAreaElement | null>(null);
const previewRef = ref<HTMLElement | null>(null);
const mobilePane = ref<'editor' | 'preview'>('editor');
const themeVersion = ref(0);
let diagramId = 0;
let themeObserver: MutationObserver | null = null;

const renderedHtml = computed(() => {
    void themeVersion.value;
    const html = markdown.parse(props.modelValue) as string;

    return DOMPurify.sanitize(html, {
        ADD_ATTR: ['target'],
        USE_PROFILES: {html: true},
    });
});

const wordCount = computed(() => {
    const text = props.modelValue
        .replace(/```(?:[\w-]+)?\n?([\s\S]*?)```/g, '$1')
        .replace(/[`*_>#\[\]()|~-]/g, ' ')
        .trim();

    return text === '' ? 0 : text.split(/\s+/u).length;
});

const readingMinutes = computed(() => wordCount.value === 0 ? 0 : Math.max(1, Math.ceil(wordCount.value / 200)));

async function renderMermaidDiagrams(): Promise<void> {
    await nextTick();

    const preview = previewRef.value;
    if (! preview) {
        return;
    }

    const blocks = Array.from(preview.querySelectorAll<HTMLElement>('pre code.language-mermaid'));
    if (blocks.length === 0) {
        return;
    }

    const {default: mermaid} = await import('mermaid');

    mermaid.initialize({
        startOnLoad: false,
        securityLevel: 'strict',
        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'neutral',
        htmlLabels: false,
    });

    for (const block of blocks) {
        const source = block.textContent?.trim() ?? '';
        const container = document.createElement('div');
        container.className = 'markdown-mermaid';
        block.parentElement?.replaceWith(container);

        try {
            await mermaid.parse(source);
            const result = await mermaid.render(`markdown-mermaid-${diagramId++}`, source);
            container.innerHTML = DOMPurify.sanitize(result.svg, {
                USE_PROFILES: {svg: true, svgFilters: true},
            });
        } catch {
            container.classList.add('markdown-mermaid-error');
            container.textContent = 'Diagramme Mermaid invalide. Vérifiez sa syntaxe.';
        }
    }
}

watch(renderedHtml, () => void renderMermaidDiagrams(), {immediate: true});

onMounted(() => {
    void renderMermaidDiagrams();
    themeObserver = new MutationObserver(() => {
        themeVersion.value += 1;
    });
    themeObserver.observe(document.documentElement, {attributes: true, attributeFilter: ['class']});
});

onBeforeUnmount(() => themeObserver?.disconnect());

async function insertSyntax(before: string, after = before, fallback = 'texte'): Promise<void> {
    const textarea = textareaRef.value;
    if (! textarea) {
        return;
    }

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selection = props.modelValue.slice(start, end) || fallback;
    const nextValue = `${props.modelValue.slice(0, start)}${before}${selection}${after}${props.modelValue.slice(end)}`;
    emit('update:modelValue', nextValue);

    await nextTick();
    textarea.focus();
    textarea.setSelectionRange(start + before.length, start + before.length + selection.length);
}

async function insertBlock(block: string): Promise<void> {
    const textarea = textareaRef.value;
    if (! textarea) {
        return;
    }

    const start = textarea.selectionStart;
    const prefix = start > 0 && props.modelValue[start - 1] !== '\n' ? '\n\n' : '';
    const nextValue = `${props.modelValue.slice(0, start)}${prefix}${block}${props.modelValue.slice(start)}`;
    emit('update:modelValue', nextValue);

    await nextTick();
    textarea.focus();
    textarea.setSelectionRange(start + prefix.length, start + prefix.length + block.length);
}

const toolbarActions = [
    {label: 'Titre', icon: Heading1, action: () => insertBlock('## Nouveau titre\n')},
    {label: 'Gras', icon: Bold, action: () => insertSyntax('**', '**', 'texte en gras')},
    {label: 'Italique', icon: Italic, action: () => insertSyntax('_', '_', 'texte en italique')},
    {label: 'Liste', icon: List, action: () => insertBlock('- Premier élément\n- Deuxième élément\n')},
    {label: 'Liste numérotée', icon: ListOrdered, action: () => insertBlock('1. Premier élément\n2. Deuxième élément\n')},
    {label: 'Citation', icon: Quote, action: () => insertBlock('> Votre citation\n')},
    {label: 'Code', icon: Code2, action: () => insertBlock('```javascript\nconst exemple = true;\n```\n')},
    {label: 'Formule', icon: Braces, action: () => insertBlock('$$\nE = mc^2\n$$\n')},
    {label: 'Lien', icon: Link2, action: () => insertSyntax('[', '](https://)', 'libellé')},
    {label: 'Image', icon: Image, action: () => insertBlock('![Description de l’image](https://exemple.com/image.png)\n')},
    {label: 'Tableau', icon: Table2, action: () => insertBlock('| Colonne 1 | Colonne 2 |\n| --- | --- |\n| Valeur 1 | Valeur 2 |\n')},
    {label: 'Diagramme Mermaid', icon: Workflow, action: () => insertBlock('```mermaid\nflowchart LR\n    A[Début] --> B[Étape]\n    B --> C[Fin]\n```\n')},
];
</script>

<template>
    <FieldWrapper :error="error" :for-id="id" :hint="hint" :label="label" :required="required">
        <div class="admin-panel min-w-0 overflow-hidden border">
            <div class="admin-divider flex flex-wrap items-center gap-1 border-b p-2">
                <button
                    v-for="action in toolbarActions"
                    :key="action.label"
                    :aria-label="action.label"
                    :title="action.label"
                    class="admin-text admin-hover grid size-8 place-items-center transition"
                    type="button"
                    @click="action.action"
                >
                    <component :is="action.icon" class="size-4" :stroke-width="1.8"/>
                </button>

                <div class="admin-divider ml-auto flex border lg:hidden">
                    <button
                        :class="mobilePane === 'editor' ? 'bg-[#a23362] text-white' : 'admin-text admin-hover'"
                        class="h-8 px-3 text-xs font-medium transition"
                        type="button"
                        @click="mobilePane = 'editor'"
                    >
                        Éditeur
                    </button>
                    <button
                        :class="mobilePane === 'preview' ? 'bg-[#a23362] text-white' : 'admin-text admin-hover'"
                        class="inline-flex h-8 items-center gap-1.5 px-3 text-xs font-medium transition"
                        type="button"
                        @click="mobilePane = 'preview'"
                    >
                        <Eye class="size-3.5" :stroke-width="1.8"/>
                        Aperçu
                    </button>
                </div>
            </div>

            <div class="grid min-w-0 lg:grid-cols-2 lg:divide-x lg:divide-[color:var(--admin-border)]">
                <section :class="mobilePane === 'editor' ? 'block' : 'hidden lg:block'" class="min-w-0">
                    <div class="admin-divider hidden h-10 items-center border-b px-4 text-[10px] font-semibold uppercase tracking-[0.12em] lg:flex">
                        Markdown
                    </div>
                    <textarea
                        :id="id"
                        ref="textareaRef"
                        :placeholder="placeholder"
                        :value="modelValue"
                        class="admin-field min-h-80 w-full resize-y border-0 bg-transparent p-4 font-mono text-sm leading-6 outline-none"
                        spellcheck="true"
                        @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                    />
                </section>

                <section :class="mobilePane === 'preview' ? 'block' : 'hidden lg:block'" class="min-w-0">
                    <div class="admin-divider hidden h-10 items-center border-b px-4 text-[10px] font-semibold uppercase tracking-[0.12em] lg:flex">
                        Aperçu en temps réel
                    </div>
                    <div
                        v-if="modelValue.trim()"
                        ref="previewRef"
                        :key="themeVersion"
                        class="markdown-preview admin-text min-h-80 min-w-0 overflow-x-auto p-5 text-sm"
                        v-html="renderedHtml"
                    />
                    <div v-else class="admin-faint grid min-h-80 place-items-center p-5 text-center text-sm">
                        L’aperçu apparaîtra ici pendant la rédaction.
                    </div>
                </section>
            </div>

            <div class="admin-divider admin-muted flex flex-wrap items-center justify-between gap-2 border-t px-4 py-2 text-[11px]">
                <span>{{ wordCount }} mot(s) · {{ readingMinutes }} min de lecture</span>
                <span>Markdown · Mermaid · KaTeX</span>
            </div>
        </div>
    </FieldWrapper>
</template>

<style scoped>
.markdown-preview {
    line-height: 1.75;
    overflow-wrap: anywhere;
}

.markdown-preview :deep(h1),
.markdown-preview :deep(h2),
.markdown-preview :deep(h3),
.markdown-preview :deep(h4) {
    color: var(--admin-heading);
    font-weight: 650;
    line-height: 1.25;
    margin: 1.5em 0 0.65em;
}

.markdown-preview :deep(h1) { font-size: 1.75rem; }
.markdown-preview :deep(h2) { border-bottom: 1px solid var(--admin-border); font-size: 1.4rem; padding-bottom: 0.35rem; }
.markdown-preview :deep(h3) { font-size: 1.15rem; }
.markdown-preview :deep(p) { margin: 0.8rem 0; }
.markdown-preview :deep(ul) { list-style: disc; margin: 0.8rem 0; padding-left: 1.5rem; }
.markdown-preview :deep(ol) { list-style: decimal; margin: 0.8rem 0; padding-left: 1.5rem; }
.markdown-preview :deep(blockquote) { border-left: 3px solid #a23362; color: var(--admin-muted); margin: 1rem 0; padding: 0.35rem 0 0.35rem 1rem; }
.markdown-preview :deep(a) { color: #c23a72; text-decoration: underline; text-underline-offset: 2px; }
.markdown-preview :deep(code) { background: var(--admin-panel-muted); color: #c23a72; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; padding: 0.15rem 0.35rem; }
.markdown-preview :deep(pre) { background: #101827; color: #e5edf8; margin: 1rem 0; max-width: 100%; overflow-x: auto; padding: 1rem; }
.markdown-preview :deep(pre code) { background: transparent; color: inherit; padding: 0; }
.markdown-preview :deep(table) { border-collapse: collapse; margin: 1rem 0; min-width: 100%; }
.markdown-preview :deep(th),
.markdown-preview :deep(td) { border: 1px solid var(--admin-border); padding: 0.55rem 0.7rem; text-align: left; }
.markdown-preview :deep(th) { background: var(--admin-panel-muted); color: var(--admin-heading); }
.markdown-preview :deep(img) { height: auto; margin: 1rem auto; max-width: 100%; }
.markdown-preview :deep(.katex-display) { max-width: 100%; overflow-x: auto; overflow-y: hidden; padding: 0.5rem 0; }
.markdown-preview :deep(.markdown-mermaid) { background: var(--admin-panel-muted); margin: 1rem 0; max-width: 100%; overflow-x: auto; padding: 1rem; text-align: center; }
.markdown-preview :deep(.markdown-mermaid svg) { height: auto; max-width: 100%; }
.markdown-preview :deep(.markdown-mermaid-error) { border: 1px solid rgb(244 63 94 / 35%); color: #fb7185; text-align: left; }
</style>
