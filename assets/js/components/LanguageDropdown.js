const { __ } = wp.i18n;
const { Dropdown, MenuGroup, MenuItem } = wp.components;

/**
 * Reusable language dropdown component
 * Used across editor sidebar and block controls
 */
export const LanguageDropdown = ({
    languages,
    onSelect,
    renderToggle,
    headerText,
    infoText,
    showBackendInfo = false,
    backendName = '',
    className = 'translation-language-dropdown'
}) => {
    return (
        <Dropdown
            className={className}
            popoverProps={{ placement: 'bottom-start' }}
            contentClassName="translation-dropdown-content"
            renderToggle={renderToggle}
            renderContent={({ onClose }) => (
                <div style={{ minWidth: '200px' }}>
                    {(headerText || showBackendInfo) && (
                        <div style={{
                            padding: '8px 12px',
                            borderBottom: '1px solid #ddd',
                            fontSize: '11px',
                            color: '#666',
                            background: '#f9f9f9'
                        }}>
                            {headerText && (
                                <div style={{ fontWeight: '600', marginBottom: '2px' }}>
                                    {headerText}
                                </div>
                            )}
                            {showBackendInfo && backendName && (
                                <div>
                                    {__('Using:', 'wordpress-openai-translation')} <strong>{backendName}</strong>
                                </div>
                            )}
                        </div>
                    )}
                    {infoText && (
                        <div style={{
                            padding: '8px 12px',
                            fontSize: '11px',
                            color: '#666',
                            background: '#f0f6fc',
                            borderBottom: '1px solid #ddd'
                        }}>
                            {infoText}
                        </div>
                    )}
                    <MenuGroup label={!headerText && !infoText ? __('Select Language', 'wordpress-openai-translation') : undefined}>
                        {languages.map((lang) => (
                            <MenuItem
                                key={lang.code}
                                onClick={() => {
                                    onSelect(lang.code);
                                    onClose();
                                }}
                                icon="translation"
                                iconPosition="left"
                            >
                                {lang.label}
                            </MenuItem>
                        ))}
                    </MenuGroup>
                </div>
            )}
        />
    );
};