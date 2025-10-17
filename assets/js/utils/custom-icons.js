/**
 * Custom SVG icons for the translation plugin
 */

const { registerBlockType } = wp.blocks || {};

/**
 * Translation Undo Icon - Custom SVG combining translation and undo symbols
 */
export const TranslationUndoIcon = () => (
    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <rect x="0" fill="none" width="20" height="20" />
        <g>
            <path d="M11 7H9.49c-.63 0-1.25.3-1.59.7L7 5H4.13l-2.39 7h1.69l.74-2H7v4H2c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2h7c1.1 0 2 .9 2 2v2zM6.51 9H4.49l1-2.93zM10 8h7c1.1 0 2 .9 2 2v7c0 1.1-.9 2-2 2h-7c-1.1 0-2-.9-2-2v-7c0-1.1.9-2 2-2zm7.25 5v-1.08h-3.17V9.75h-1.16v2.17H9.75V13h1.28c.11.85.56 1.85 1.28 2.62-.87.36-1.89.62-2.31.62-.01.02.22.97.2 1.46.84 0 2.21-.5 3.28-1.15 1.09.65 2.48 1.15 3.34 1.15-.02-.49.2-1.44.2-1.46-.43 0-1.49-.27-2.38-.63.7-.77 1.14-1.77 1.25-2.61h1.36zm-3.81 1.93c-.5-.46-.85-1.13-1.01-1.93h2.09c-.17.8-.51 1.47-1 1.93l-.04.03s-.03-.02-.04-.03z" />
        </g>
        <g transform="matrix(0.49483711,0,0,0.49483711,8.6842548,-0.80509127)">
            <path d="M 12,5 H 7 V 2 L 1,6 7,10 V 7 h 5 c 2.2,0 4,1.8 4,4 0,2.2 -1.8,4 -4,4 H 7 v 2 h 5 c 3.3,0 6,-2.7 6,-6 0,-3.3 -2.7,-6 -6,-6 z" />
        </g>
    </svg>
);