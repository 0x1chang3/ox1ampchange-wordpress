<?php
/**
 *
 * Plugin Name: Change AMPHTML
 * Description: AMP sayfaları için amphtml etiketi domainini değiştirir. Ücretsiz bir yazılımdır. Güncel sürümünü github sayfamdan edinebilirsiniz.
 * Version:     1.0
 * Author:      0x1change
 * Plugin URI:  https://github.com/0x1chang3/ox1ampchange-wordpress
 * Author URI:  https://sansursuzinternet.org
 * License: GNU General Public License v3.0
 *
 */

function buffer_bunny( $finder ) {
    if ( strpos( $finder, 'https://cdn.ampproject.org/' ) !== false && !is_admin() ) {
        $pure_domain = str_replace(['https://', 'http://'], null, get_site_url());
        $prefix = is_ssl() ? 'https://' : 'http://';

        $dom = new DOMDocument();
        @$dom->loadHTML($finder, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        $elements = $xpath->query('//amp-img | //img | //amp-anim');

        foreach ($elements as $element) {
            if ($element->hasAttribute('src')) {
                $src = $element->getAttribute('src');
                $new_src = preg_replace('/https?:\/\/.*?\//', $prefix . 'i0.wp.com/' . $pure_domain . '/', $src);
                $element->setAttribute('src', $new_src);
            }

            if ($element->hasAttribute('srcset')) {
                $srcset = $element->getAttribute('srcset');
                $new_srcset = preg_replace('/https?:\/\/.*?\//', $prefix . 'i0.wp.com/' . $pure_domain . '/', $srcset);
                $element->setAttribute('srcset', $new_srcset);
            }
        }

        $finder = $dom->saveHTML();
    }

    $http_version = is_ssl() ? 'https://' : 'http://';
    $addon = !empty(get_option('0x1change__amphtml')) ? get_option('0x1change__amphtml') : str_replace(['https://', 'http://'], null, get_site_url());

    $finder = str_replace(
        '<link rel="amphtml" href="'.get_site_url(),
        '<link rel="amphtml" href="'.$http_version . $addon,
        $finder
    );

    $finder = str_replace(
        "<link rel='amphtml' href='".get_site_url(),
        "<link rel='amphtml' href='".$http_version . $addon,
        $finder
    );

    return $finder;
}

function buffer_bunny_start() {
    if (function_exists('buffer_bunny')) {
        ob_start('buffer_bunny');
    }
}

function buffer_bunny_end() {
    if (function_exists('buffer_bunny') && ob_start('buffer_bunny') === true) {
        ob_end_flush();
    }
}

add_action('after_setup_theme', 'buffer_bunny_start');
add_action('shutdown', 'buffer_bunny_end');

add_filter('plugin_action_links_change-amphtml-main/change-amphtml.php', function ($links_array){
    array_unshift($links_array, '<a href="'.get_admin_url().'options-general.php?page=0x1change_amphtml_group">Ayarlar</a>');
    return $links_array;
});

add_action('admin_init', function () {
    register_setting('0x1change_amphtml_group', '0x1change__amphtml');
});

add_action('admin_menu', function(){
    add_options_page(
        'AMPHTML',
        'AMPHTML',
        'manage_options',
        '0x1change_amphtml_group',
        function(){
            ?>
            <style>
                .submit {
                    padding: 0;
                }
                p.submit {
                    margin-top: 0 !important;
                }
            </style>
            <div style="width: fit-content; background: white; padding:15px; margin:10% auto 0; border-radius:7px;box-shadow: 1px 0 25px rgba(0, 0, 0, .1)">
                <div>
                    <form method="post" action="options.php">
                        <?php settings_fields('0x1change_amphtml_group'); ?>
                        <?php do_settings_sections('0x1change_amphtml_group'); ?>
                        <label>
                            <div style="margin-bottom:10px">
                                Kutuya <strong>sadece domain</strong> yazın. <br> Örneğin: sub.domain.com veya ornek.com gibi.
                            </div>
                            <input type="text" name="0x1change__amphtml" placeholder="AMPHTML içeriği" value="<?php echo get_option('0x1change__amphtml') ?>">
                        </label>
                        <?php submit_button(); ?>
                        <p>Bu eklenti <strong>0x1change</strong> tarafından <strong>ücretsiz</strong> promosyon olarak verilmektedir.</p>
                    </form>
                </div>
            </div>
            <?php
        }
    );
});
