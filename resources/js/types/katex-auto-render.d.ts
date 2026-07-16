declare module 'katex/contrib/auto-render' {
    interface MathDelimiter {
        left: string;
        right: string;
        display: boolean;
    }

    interface AutoRenderOptions {
        delimiters?: MathDelimiter[];
        throwOnError?: boolean;
    }

    export default function renderMathInElement(element: HTMLElement, options?: AutoRenderOptions): void;
}
