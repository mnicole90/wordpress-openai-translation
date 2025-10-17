const { __ } = wp.i18n;
const { useState, useEffect } = wp.element;
const { BlockControls } = wp.blockEditor;
const { ToolbarGroup, ToolbarButton, Spinner } = wp.components;
const { useDispatch, useSelect } = wp.data;

import { BackendManager } from '../utils/backend-manager';
import { LanguageDropdown } from '../components/LanguageDropdown';
import { translateSingleBlock, updateBlockRecursively } from '../utils/translation-api';
import { TranslationUndoIcon } from '../utils/custom-icons';

// BACKUP STRATEGY: Using serialization-based approach (most reliable)
import { 
    createBlockBackup, 
    attachBackupToAttributes, 
    hasBackup, 
    getBackup, 
    restoreFromBackup 
} from '../utils/backup-strategy-serialize';

/**
 * Unified Translation Control - handles both translation and rollback
 * This avoids React reconciliation issues from conditional component mounting
 */
export const UnifiedTranslationControl = ({ clientId, attributes, name }) => {
    // Get supported block types from backend configuration
    const supportedBlocks = window.translationConfig?.supportedBlocks || [];

    // Early return for unsupported blocks - BEFORE any hooks
    if (!supportedBlocks.includes(name)) {
        return null;
    }

    // Now we can safely use hooks
    const [isTranslating, setIsTranslating] = useState(false);
    const [currentBackend, setCurrentBackend] = useState('');
    const { updateBlockAttributes, replaceBlock } = useDispatch('core/block-editor');

    // Get full block structure including innerBlocks
    const fullBlock = useSelect((select) => {
        return select('core/block-editor').getBlock(clientId);
    }, [clientId]);

    // Check if block has backup
    const blockHasBackup = hasBackup(attributes);

    // Sync with global backend selection
    useEffect(() => {
        const backend = BackendManager.getSelectedBackend();
        setCurrentBackend(backend);

        const unsubscribe = BackendManager.onBackendChange((newBackend) => {
            setCurrentBackend(newBackend);
        });

        return unsubscribe;
    }, []);

    const languages = window.translationConfig?.languages || [];
    const restNamespace = window.translationConfig?.restNamespace || 'wp-translation/v1';

    // Translation handler
    const translateBlock = async (language, backend = currentBackend) => {
        if (isTranslating) return;

        setIsTranslating(true);

        try {
            const backendName = BackendManager.getBackendName(backend);
            
            // Create backup using native Gutenberg serialization
            const backup = createBlockBackup(fullBlock, {
                language,
                backend,
                backendName
            });

            // Prepare block for API (include innerBlocks)
            const blockToTranslate = {
                clientId: fullBlock.clientId,
                name: fullBlock.name,
                attributes: { ...fullBlock.attributes },
                innerBlocks: fullBlock.innerBlocks || []
            };

            const response = await translateSingleBlock({
                backend,
                block: blockToTranslate,
                language,
                restNamespace
            });

            // Get translated block with full structure
            const translatedBlock = response.translatedBlock;

            // Attach backup to translated attributes
            const attributesWithBackup = attachBackupToAttributes(
                translatedBlock.attributes,
                backup,
                language,
                backend,
                backendName
            );

            // Update root block attributes (with backup attached)
            updateBlockAttributes(clientId, attributesWithBackup);

            // Recursively update innerBlocks if they exist
            if (translatedBlock.innerBlocks && translatedBlock.innerBlocks.length > 0) {
                translatedBlock.innerBlocks.forEach((innerBlock) => {
                    updateBlockRecursively(innerBlock, updateBlockAttributes);
                });
            }

        } catch (error) {
            console.error('Block translation failed:', error);
            alert(error.message || __('Translation failed', 'wordpress-openai-translation'));
        } finally {
            setIsTranslating(false);
        }
    };

    // Rollback handler
    const handleRollback = () => {
        const backup = getBackup(attributes);
        
        if (!backup) {
            alert(__('No backup found', 'wordpress-openai-translation'));
            return;
        }

        // Build confirmation message
        const backendInfo = backup.backendName || backup.backend || 'Unknown';
        const translationDate = backup.timestamp
            ? new Date(backup.timestamp).toLocaleString()
            : '';

        let confirmMessage = __('Restore original content?', 'wordpress-openai-translation');
        confirmMessage += '\n\n';
        confirmMessage += __('Translation by:', 'wordpress-openai-translation') + ' ' + backendInfo;
        if (translationDate) {
            confirmMessage += '\n' + __('Date:', 'wordpress-openai-translation') + ' ' + translationDate;
        }

        if (!confirm(confirmMessage)) {
            return;
        }

        // Restore using native Gutenberg parser
        const restoredBlock = restoreFromBackup(backup);
        
        if (!restoredBlock) {
            alert(__('Unable to restore: backup format not recognized', 'wordpress-openai-translation'));
            return;
        }

        // Use Gutenberg's replaceBlock API - handles everything correctly
        replaceBlock(clientId, restoredBlock);
    };

    // Register for batch translation
    window.translateBlockAsync = window.translateBlockAsync || {};
    window.translateBlockAsync[clientId] = (language, backend) => {
        return translateBlock(language, backend);
    };

    // Render either translate or undo button based on backup state
    const backendName = BackendManager.getBackendName(currentBackend);

    if (blockHasBackup) {
        // Show UNDO button
        const backup = getBackup(attributes);
        const backendInfo = backup?.backendName || backup?.backend || '';
        const translationDate = backup?.timestamp ? new Date(backup.timestamp).toLocaleString() : '';

        let tooltipText = __('Undo Translation', 'wordpress-openai-translation');
        if (backendInfo) {
            tooltipText += ' (' + backendInfo;
            if (translationDate) {
                tooltipText += ', ' + translationDate;
            }
            tooltipText += ')';
        }

        return (
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon={<TranslationUndoIcon />}
                        label={tooltipText}
                        onClick={handleRollback}
                        className="block-rollback-button"
                        style={{
                            color: '#d94f4f',
                        }}
                    />
                </ToolbarGroup>
            </BlockControls>
        );
    } else {
        // Show TRANSLATE button
        const tooltipLabel = isTranslating
            ? __('Translating Block...', 'wordpress-openai-translation')
            : __('Translate Block', 'wordpress-openai-translation') + ' (' + backendName + ')';

        return (
            <BlockControls>
                <ToolbarGroup>
                    <LanguageDropdown
                        languages={languages}
                        onSelect={(language) => translateBlock(language, currentBackend)}
                        headerText={__('Block Translation', 'wordpress-openai-translation')}
                        showBackendInfo={true}
                        backendName={backendName}
                        renderToggle={({ isOpen, onToggle }) => (
                            <ToolbarButton
                                icon={isTranslating ? undefined : 'translation'}
                                label={tooltipLabel}
                                onClick={onToggle}
                                aria-expanded={isOpen}
                                disabled={isTranslating}
                                style={{
                                    color: isTranslating ? '#666' : '#2271b1'
                                }}
                            >
                                {isTranslating && <Spinner style={{ margin: 0 }} />}
                            </ToolbarButton>
                        )}
                    />
                </ToolbarGroup>
            </BlockControls>
        );
    }
};