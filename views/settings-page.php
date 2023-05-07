<?php
$validatorName = esc_attr(get_option('openai_translation_validator_name'));
?>
<div class="wrap">
    <h1>OpenAI Translation Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('openai-translation'); ?>
        <?php do_settings_sections('openai-translation'); ?>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="api_key">Clé API :</label></th>
                <td><input type="password" name="openai_translation_api_key" id="api_key" class="regular-text"
                           value="<?php echo esc_attr(get_option('openai_translation_api_key')); ?>"/></td>
            </tr>
            <tr>
                <th scope="row"><label for="validator_name">Locale validator :</label></th>
                <td><select name="openai_translation_validator_name" id="validator_name">
                        <option
                            <?= $validatorName === 'custom' ? 'selected="selected"' : '' ?>
                            value="custom">
                            Default Validator
                        </option>
                        <option
                            <?= $validatorName === 'symfony' ? 'selected="selected"' : '' ?>
                            value="symfony">
                            Symfony Locale Validator
                        </option>
                    </select>
            </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
