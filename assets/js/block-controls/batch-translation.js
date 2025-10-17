const { __ } = wp.i18n;
const { useState, useEffect } = wp.element;
const { useSelect, useDispatch } = wp.data;
const { Button, Spinner } = wp.components;

export const BatchTranslationControl = () => {
    const [isBatchTranslating, setIsBatchTranslating] = useState(false);
    const [translatingBlocks, setTranslatingBlocks] = useState(new Set());
    const [translationProgress, setTranslationProgress] = useState({ current: 0, total: 0 });

    // Get selected blocks
    const { selectedBlocks, hasSelection } = useSelect((select) => {
        const selectedClientIds = select('core/block-editor').getMultiSelectedBlockClientIds();
        const blocks = selectedClientIds.map(id => ({
            clientId: id,
            ...select('core/block-editor').getBlock(id)
        }));

        return {
            selectedBlocks: blocks,
            hasSelection: selectedClientIds.length > 1, // At least 2 blocks
        };
    }, []);

    const translateMultipleBlocks = async (language, backend) => {
        if (isBatchTranslating || selectedBlocks.length === 0) return;

        const supportedBlocks = ['core/paragraph', 'core/heading', 'core/list', 'core/quote'];
        const blocksToTranslate = selectedBlocks.filter(block =>
            supportedBlocks.includes(block.name)
        );

        if (blocksToTranslate.length === 0) {
            alert(__('No translatable blocks selected', 'wordpress-openai-translation'));
            return;
        }

        setIsBatchTranslating(true);
        setTranslationProgress({ current: 0, total: blocksToTranslate.length });

        // Translate blocks asynchronously (non-blocking)
        const translationPromises = blocksToTranslate.map(async (block, index) => {
            const { clientId } = block;

            // Mark block as translating
            setTranslatingBlocks(prev => new Set([...prev, clientId]));

            try {
                // Use the async translation function registered by BlockTranslationControl
                if (window.translateBlockAsync && window.translateBlockAsync[clientId]) {
                    await window.translateBlockAsync[clientId](language, backend);
                }
            } catch (error) {
                console.error(`Failed to translate block ${clientId}:`, error);
            } finally {
                // Update progress
                setTranslationProgress(prev => ({
                    ...prev,
                    current: prev.current + 1
                }));

                // Remove from translating set
                setTranslatingBlocks(prev => {
                    const newSet = new Set(prev);
                    newSet.delete(clientId);
                    return newSet;
                });
            }
        });

        // Wait for all translations to complete
        await Promise.all(translationPromises);

        setIsBatchTranslating(false);
        setTranslationProgress({ current: 0, total: 0 });
    };

    // Expose batch translation function globally
    useEffect(() => {
        window.batchTranslateBlocks = translateMultipleBlocks;
    }, [selectedBlocks, isBatchTranslating]);

    return null; // This is a controller component, no UI needed
};

// Component to show batch translation status in the sidebar
export const BatchTranslationStatus = () => {
    const [progress, setProgress] = useState({ current: 0, total: 0 });
    const [isActive, setIsActive] = useState(false);

    useEffect(() => {
        // Poll for translation progress
        const checkProgress = () => {
            if (window.batchTranslationProgress) {
                setProgress(window.batchTranslationProgress);
                setIsActive(window.batchTranslationProgress.total > 0);
            }
        };

        const interval = setInterval(checkProgress, 100);
        return () => clearInterval(interval);
    }, []);

    if (!isActive || progress.total === 0) {
        return null;
    }

    const percentage = Math.round((progress.current / progress.total) * 100);

    return (
        <div style={{
            padding: '12px',
            background: '#f0f0f0',
            borderRadius: '4px',
            marginBottom: '12px',
            display: 'flex',
            alignItems: 'center',
            gap: '8px'
        }}>
            <Spinner style={{ margin: 0 }} />
            <div style={{ flex: 1 }}>
                <div style={{ fontSize: '12px', marginBottom: '4px' }}>
                    {__('Translating blocks...', 'wordpress-openai-translation')}
                </div>
                <div style={{ fontSize: '11px', color: '#666' }}>
                    {progress.current} / {progress.total} ({percentage}%)
                </div>
            </div>
        </div>
    );
};