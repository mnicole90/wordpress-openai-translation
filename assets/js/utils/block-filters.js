/**
 * Filter blocks to exclude individually translated ones
 * Recursively processes nested blocks (groups, columns, etc.)
 */
export const filterUntranslatedBlocks = (blocks) => {
    return blocks.map(block => {
        // Skip this block if it was individually translated
        if (block.attributes._individuallyTranslated) {
            return null;
        }

        // Recursively filter inner blocks
        if (block.innerBlocks && block.innerBlocks.length > 0) {
            const filteredInnerBlocks = filterUntranslatedBlocks(block.innerBlocks)
                .filter(b => b !== null);
            
            // If all inner blocks are filtered out, skip this container too
            if (filteredInnerBlocks.length === 0 && block.innerBlocks.length > 0) {
                return null;
            }

            return {
                ...block,
                innerBlocks: filteredInnerBlocks
            };
        }

        return block;
    }).filter(b => b !== null);
};