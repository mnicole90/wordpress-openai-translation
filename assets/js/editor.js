const { __ } = wp.i18n;
const { registerPlugin } = wp.plugins;
const { useState } = wp.element;
const { PanelBody, PanelRow, Button, Dropdown, MenuGroup, MenuItem, Spinner } = wp.components;
const { PluginSidebar } = wp.editPost;
const { useSelect, useDispatch } = wp.data;
const apiFetch = wp.apiFetch;

const TranslationPanel = () => {
    const [isTranslating, setIsTranslating] = useState(false);

    // Get current post title and blocks from the editor
    const { title, blocks } = useSelect((select) => ({
        title: select('core/editor').getEditedPostAttribute('title'),
        blocks: select('core/block-editor').getBlocks(),
    }), []);

    // Get dispatch functions for updating editor content
    const { editPost } = useDispatch('core/editor');
    const { replaceBlocks } = useDispatch('core/block-editor');

    const handleTranslation = async (language) => {
        setIsTranslating(true);

        try {
            const response = await apiFetch({
                path: '/openai-translation/v1/translate',
                method: 'POST',
                data: {
                    title,
                    blocks,
                    language,
                },
            });

            // Update the post title
            editPost({ title: response.title });

            // Replace blocks with translated content
            response.blocks.forEach((block) => {
                const newBlock = wp.blocks.createBlock(
                    block.name,
                    {
                        ...block.attributes,
                        dropCap: block.attributes.dropCap === 'true' || block.attributes.dropCap === true,
                    }
                );
                replaceBlocks(block.clientId, newBlock);
            });
        } catch (error) {
            alert(error.message || __('Translation failed', 'wordpress-openai-translation'));
        } finally {
            setIsTranslating(false);
        }
    };

    const languages = [
        { code: 'en_GB', label: __('English (GB)', 'wordpress-openai-translation') },
        { code: 'en_US', label: __('English (US)', 'wordpress-openai-translation') },
        { code: 'fr_FR', label: __('French', 'wordpress-openai-translation') },
        { code: 'es_ES', label: __('Spanish', 'wordpress-openai-translation') },
        { code: 'de_DE', label: __('German', 'wordpress-openai-translation') },
        { code: 'it_IT', label: __('Italian', 'wordpress-openai-translation') },
        { code: 'ja_JP', label: __('Japanese', 'wordpress-openai-translation') },
    ];

    return (
        <PanelBody title={__('Translation', 'wordpress-openai-translation')}>
            <PanelRow>
                <div style={{ width: '100%' }}>
                    <Dropdown
                        className="translation-dropdown"
                        contentClassName="translation-dropdown-content"
                        position="bottom left"
                        renderToggle={({ isOpen, onToggle }) => (
                            <Button
                                onClick={onToggle}
                                aria-expanded={isOpen}
                                disabled={isTranslating}
                                variant="secondary"
                                style={{ width: '100%' }}
                            >
                                {isTranslating ? (
                                    <>
                                        <Spinner />
                                        {__('Translating...', 'wordpress-openai-translation')}
                                    </>
                                ) : (
                                    __('Translate to...', 'wordpress-openai-translation')
                                )}
                            </Button>
                        )}
                        renderContent={({ onClose }) => (
                            <MenuGroup label={__('Select Language', 'wordpress-openai-translation')}>
                                {languages.map((lang) => (
                                    <MenuItem
                                        key={lang.code}
                                        onClick={() => {
                                            handleTranslation(lang.code);
                                            onClose();
                                        }}
                                    >
                                        {lang.label}
                                    </MenuItem>
                                ))}
                            </MenuGroup>
                        )}
                    />
                </div>
            </PanelRow>
        </PanelBody>
    );
};

const TranslationSidebar = () => {
    return (
        <PluginSidebar
            name="openai-translation-sidebar"
            title={__('OpenAI Translation', 'wordpress-openai-translation')}
            icon="translation"
        >
            <TranslationPanel />
        </PluginSidebar>
    );
};

registerPlugin('openai-translation-plugin', {
    render: TranslationSidebar,
});
