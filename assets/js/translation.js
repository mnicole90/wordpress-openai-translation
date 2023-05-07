jQuery(document).ready(function ($) {
    setTimeout(function () {
        // Add a button with dropdown to the toolbar
        $('.edit-post-header__settings')
            .prepend(wp.media.template('translation-button')());
    }, 1000);

    // When the button is clicked
    $(document).on('click', '#translation-button', function () {
        // Get the position of the button
        const position = $(this).offset();
        const sidebarWidth = $('.interface-interface-skeleton__sidebar').width();
        $('.block-editor-translation__popover')
            .css('display', 'block')
            .css('transform', 'translateX(' + (position.left - sidebarWidth) + 'px) translateY(' + (position.top + 38) + 'px) translateY(0em) scale(1) translateZ(0px)');
    });

    // When a language is clicked
    $(document).on('click', '.block-editor-inserter__quick-inserter button', function () {

        // Hide the popover
        $('.block-editor-translation__popover')
            .css('display', 'none')

        // Show the spinner
        $('#translate-spinner')
            .css('display', 'inline-block')
            .css('margin-left', '5px')
        ;

        // Get the variables
        const language = $(this).data('language');
        const title = wp.data.select("core/editor").getEditedPostAttribute('title');
        const blocks = wp.data.select("core/block-editor").getBlocks();

        // Call the API
        $.ajax({
            url: wpApiSettings.root + 'openai-translation/v1/translate',
            method: 'POST',
            data: {
                title: title,
                blocks: blocks,
                language: language,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
        }).done(function (data) {
            // Replace the title
            wp.data.dispatch("core/editor").editPost({title: data.title});

            // Replace the blocks
            data.blocks.forEach(function (block) {
                let newBlock = wp.blocks.createBlock(block.name, block.attributes);
                newBlock.attributes.dropCap = block.attributes.dropCap === 'true';
                wp.data.dispatch("core/block-editor").replaceBlocks(block.clientId, newBlock);
            });

            // Hide the spinner
            $('#translate-spinner')
                .css('display', 'none');

        }).fail(function (jqXHR) {
            alert(jqXHR.responseJSON.message);

            // Hide the spinner
            $('#translate-spinner')
                .css('display', 'none');
        });
    });
});
