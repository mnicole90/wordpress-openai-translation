/**
 * Serialization-based backup strategy
 * Uses Gutenberg's native block serialization for maximum reliability
 * This is the simplest and most robust approach
 */

const { serialize, parse } = wp.blocks;

/**
 * Create a backup using native Gutenberg serialization
 * @param {Object} block - The block to backup
 * @param {Object} metadata - Additional metadata (language, backend, backendName)
 * @returns {Object} Backup object with serialized content
 */
export const createBlockBackup = (block, metadata = {}) => {
    // Use Gutenberg's native serialization - handles ALL edge cases
    const serialized = serialize(block);
    
    return {
        serialized,
        timestamp: Date.now(),
        ...metadata
    };
};

/**
 * Attach backup to block attributes
 * IMPORTANT: Preserves existing backup to prevent overwriting original content
 * 
 * @param {Object} attributes - Current block attributes
 * @param {Object} backup - Backup object (only used if no backup exists)
 * @param {string} language - Target language
 * @param {string} backend - Backend identifier
 * @param {string} backendName - Backend display name
 * @returns {Object} Updated attributes with backup
 */
export const attachBackupToAttributes = (attributes, backup, language, backend, backendName) => {
    // CRITICAL: Check if backup already exists - if so, PRESERVE it!
    // This prevents overwriting the original content on re-translation
    const existingBackup = attributes?._translationBackup;
    
    return {
        ...attributes,
        _translationBackup: existingBackup || {  // Use existing if available
            ...backup,
            language,
            backend,
            backendName,
        },
        _individuallyTranslated: {
            timestamp: Date.now(),
            language,
            backend,
        }
    };
};

/**
 * Check if block has backup
 * @param {Object} attributes - Block attributes
 * @returns {boolean}
 */
export const hasBackup = (attributes) => {
    return attributes && attributes._translationBackup !== undefined;
};

/**
 * Get backup from attributes
 * @param {Object} attributes - Block attributes
 * @returns {Object|null}
 */
export const getBackup = (attributes) => {
    return attributes?._translationBackup || null;
};

/**
 * Restore block from serialized backup
 * Uses Gutenberg's parse() to reconstruct the block perfectly
 * @param {Object} backup - Backup object with serialized content
 * @returns {Object|null} Parsed block or null
 */
export const restoreFromBackup = (backup) => {
    if (!backup || !backup.serialized) {
        return null;
    }
    
    // Use Gutenberg's native parser - handles ALL edge cases
    const blocks = parse(backup.serialized);
    
    // Return the first (and should be only) block
    return blocks[0] || null;
};