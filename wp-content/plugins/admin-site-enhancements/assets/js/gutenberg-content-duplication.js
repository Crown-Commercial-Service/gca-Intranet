// Ref: https://plugins.trac.wordpress.org/browser/duplicate-page/tags/4.5/js/editor-script.js
var el = wp.element.createElement;
var __ = wp.i18n.__;
var registerPlugin = wp.plugins.registerPlugin;
var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
var buttonControl = wp.components.Button;

function asenhaContentDuplicationButton({}) {
    return el(
        PluginPostStatusInfo,
        {
            className: 'asenha-content-duplication-status-info'
        },
        el(
            buttonControl,
            {
                variant: 'secondary',
                name: 'asenha_gutenberg_content_duplication_link',
                isLink: true,
                title: cd_params.cd_post_title,
                href : cd_params.cd_duplicate_link+"&post="+cd_params.cd_post_id+"&nonce="+cd_params.cd_nonce
            }, cd_params.cd_post_text
        )
    );
}

registerPlugin( 'asenha-content-duplication-status-info-plugin', {
    render: asenhaContentDuplicationButton
} );