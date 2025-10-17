const { __ } = wp.i18n;
const apiFetch = wp.apiFetch;

/**
 * Wrapper for editor locking during translation operations
 */
export const withEditorLock = async (dispatch, callback) => {
    const { lockPostSaving, unlockPostSaving, lockPostAutosaving, unlockPostAutosaving } = dispatch('core/editor');
    
    lockPostSaving('translation-in-progress');
    lockPostAutosaving('translation-in-progress');
    
    try {
        return await callback();
    } finally {
        unlockPostSaving('translation-in-progress');
        unlockPostAutosaving('translation-in-progress');
    }
};

/**
 * Translate full page (title + blocks)
 */
export const translateFullPage = async ({ backend, title, blocks, language, restNamespace }) => {
    const response = await apiFetch({
        path: `/${restNamespace}/translate-${backend}`,
        method: 'POST',
        data: {
            title,
            blocks,
            language,
        },
    });

    if (response.errors) {
        const errorMessages = Object.values(response.errors).join(', ');
        throw new Error(errorMessages);
    }

    return response;
};

/**
 * Translate a single block
 * Uses the same unified endpoint as translateFullPage
 */
export const translateSingleBlock = async ({ backend, block, language, restNamespace }) => {
    // Use unified translation endpoint (same as translateFullPage)
    const response = await apiFetch({
        path: `/${restNamespace}/translate-${backend}`,
        method: 'POST',
        data: {
            title: '', // Empty title for block-only translation
            blocks: [block], // Wrap single block in array
            language,
        },
    });

    if (response.errors) {
        const errorMessages = Object.values(response.errors).join(', ');
        throw new Error(errorMessages);
    }

    // Unwrap single block from array response
    const translatedBlock = response.blocks?.[0];
    if (!translatedBlock) {
        throw new Error(__('No translated content returned', 'wordpress-openai-translation'));
    }

    return {
        translatedBlock,
        blockType: block.name || '',
    };
};

/**
 * Recursively update block attributes without recreating blocks
 * Preserves container blocks and only updates primitive blocks
 */
export const updateBlockRecursively = (translatedBlock, updateBlockAttributes) => {
    if (translatedBlock.attributes) {
        updateBlockAttributes(
            translatedBlock.clientId,
            translatedBlock.attributes
        );
    }

    if (translatedBlock.innerBlocks && translatedBlock.innerBlocks.length > 0) {
        translatedBlock.innerBlocks.forEach((innerBlock) => {
            updateBlockRecursively(innerBlock, updateBlockAttributes);
        });
    }
};