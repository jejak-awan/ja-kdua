/**
 * Article Content Styles & Code Block Enhancement
 * Auto-initializing script - just include this file!
 * 
 * Usage in theme Blade file:
 * <link rel="stylesheet" href="{{ asset('vendor/ja-cms/article-content.css') }}">
 * <script src="{{ asset('vendor/ja-cms/article-content.js') }}" defer></script>
 */

(function () {
    'use strict';

    // Inject CSS styles
    const styles = `
    /* Code Block Container */
    .prose pre,
    article pre,
    .article-content pre {
        position: relative;
        background-color: #1e1e2e;
        border-radius: 0.5rem;
        margin: 1.5rem 0;
        overflow: hidden;
        border: 1px solid #313244;
    }

    /* Code Block Header */
    .prose pre::before,
    article pre::before,
    .article-content pre::before {
        content: 'Code';
        display: block;
        padding: 0.5rem 1rem;
        padding-right: 4rem;
        background: #2d2d3a;
        border-bottom: 1px solid #313244;
        font-size: 0.75rem;
        font-family: ui-monospace, SFMono-Regular, monospace;
        color: #a6adc8;
    }

    /* Copy Button */
    .code-copy-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.75rem;
        padding: 0.25rem 0.6rem;
        border-radius: 0.25rem;
        background: #3d3d4a;
        border: 1px solid #45475a;
        color: #a6adc8;
        font-size: 0.7rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.15s;
        z-index: 10;
    }

    .code-copy-btn:hover {
        background: #45475a;
        color: #cdd6f4;
    }

    .code-copy-btn.copied {
        background: #22c55e;
        color: #fff;
    }

    /* Code Content */
    .prose pre code,
    article pre code,
    .article-content pre code {
        display: block;
        padding: 1rem;
        overflow-x: auto;
        font-size: 0.875rem;
        font-family: ui-monospace, SFMono-Regular, 'SF Mono', Menlo, Consolas, monospace;
        line-height: 1.7;
        color: #cdd6f4;
        background: transparent !important;
        border: none !important;
    }

    /* Inline Code */
    .prose code:not(pre code),
    article code:not(pre code),
    .article-content code:not(pre code) {
        background-color: #313244;
        border-radius: 0.25rem;
        padding: 0.125rem 0.375rem;
        font-family: ui-monospace, SFMono-Regular, monospace;
        font-size: 0.875em;
        color: #f38ba8;
    }

    /* Syntax Highlighting */
    pre .hljs-comment, pre .hljs-quote { color: #6c7086; font-style: italic; }
    pre .hljs-keyword, pre .hljs-selector-tag, pre .hljs-built_in { color: #cba6f7; }
    pre .hljs-string, pre .hljs-attr { color: #a6e3a1; }
    pre .hljs-title, pre .hljs-section { color: #89b4fa; }
    pre .hljs-number, pre .hljs-literal { color: #fab387; }
    pre .hljs-variable, pre .hljs-template-variable { color: #f38ba8; }
    pre .hljs-type, pre .hljs-class { color: #f9e2af; }
    pre .hljs-function { color: #89b4fa; }
    pre .hljs-meta { color: #f5c2e7; }

    /* Table Styling */
    .prose table,
    article table,
    .article-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 2px solid #313244;
    }

    .prose th, article th, .article-content th,
    .prose td, article td, .article-content td {
        padding: 0.75rem 1rem;
        border: 1px solid #313244;
        text-align: left;
    }

    .prose th, article th, .article-content th {
        background-color: rgba(139, 92, 246, 0.15);
        font-weight: 600;
        border-bottom: 2px solid rgba(139, 92, 246, 0.3);
    }

    .prose tr:nth-child(even) td,
    article tr:nth-child(even) td,
    .article-content tr:nth-child(even) td {
        background-color: rgba(45, 45, 58, 0.3);
    }

    /* Highlighted Text */
    mark {
        background-color: #f9e2af;
        color: #1e1e2e;
        padding: 0.125rem 0.25rem;
        border-radius: 0.125rem;
    }
    `;

    // Create style element
    const styleEl = document.createElement('style');
    styleEl.textContent = styles;
    document.head.appendChild(styleEl);

    // Enhance code blocks with copy button
    function enhanceCodeBlocks() {
        document.querySelectorAll('pre:not([data-enhanced])').forEach(function (pre) {
            pre.setAttribute('data-enhanced', 'true');

            const code = pre.querySelector('code');
            if (!code) return;

            // Create copy button
            const btn = document.createElement('button');
            btn.className = 'code-copy-btn';
            btn.innerHTML = 'Copy';
            btn.title = 'Copy code';

            btn.addEventListener('click', function () {
                const text = code.textContent || code.innerText;
                navigator.clipboard.writeText(text).then(function () {
                    btn.classList.add('copied');
                    btn.innerHTML = 'Copied!';
                    setTimeout(function () {
                        btn.classList.remove('copied');
                        btn.innerHTML = 'Copy';
                    }, 2000);
                });
            });

            pre.insertBefore(btn, pre.firstChild);
        });
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceCodeBlocks);
    } else {
        enhanceCodeBlocks();
    }

    // Export for SPA use
    window.enhanceCodeBlocks = enhanceCodeBlocks;
})();
