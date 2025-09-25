<?php
/*
Plugin Name: IP Matcher
Description: Проверяет содержится ли IP-адрес в заданных подсетях
Version: 1.0
Author: Moddix
*/

use Moddix\IpMatcher\IpMatcher;

// Добавляем страницу настроек
add_action('admin_menu', function() {
    add_options_page(
        'IP Matcher Settings',
        'IP Matcher',
        'manage_options',
        'ip-matcher-settings',
        'ip_matcher_settings_page'
    );
});

// Регистрация опций и поля через Settings API
add_action('admin_init', function() {
    // Регистрируем textarea
    register_setting('ip_matcher_settings_group', 'ip_matcher_textarea');
    // Регистрируем json результат, если потребуется выводить в админке
    register_setting('ip_matcher_settings_group', 'ip_matcher_json');

    add_settings_section(
        'ip_matcher_main_section',
        'Основные настройки',
        null,
        'ip-matcher-settings'
    );

    add_settings_field(
        'ip_matcher_textarea',
        'Список IP-адресов и подсетей (по одному на строку)',
        function() {
            $value = esc_textarea(get_option('ip_matcher_textarea', ''));
            echo "<textarea name='ip_matcher_textarea' rows='10' cols='50'>{$value}</textarea>";
        },
        'ip-matcher-settings',
        'ip_matcher_main_section'
    );
});

// 3. Вывод страницы и обработка сохранения
function ip_matcher_settings_page() {
    ?>
    <div class="wrap">
        <h1>Настройки IP Matcher</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ip_matcher_settings_group');
            do_settings_sections('ip-matcher-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php

    // После сохранения вызываем обработку и сохраняем JSON
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        $string = get_option('ip_matcher_textarea', '');
        $data = array_map('trim', explode("\n", $string));

        $matcher = new IpMatcher();
        $errors = [];
        foreach ($data as $ip) {
            if (!empty($ip)) {
                $result = $matcher->addSubnet($ip);
                if (!$result) {
                    $errors[] = $ip;
                }
            }
        }

        $matcher->prepare();
        $subnets = $matcher->getSubnets();

        // Сохраняем результат
        $json = json_encode($subnets);
        update_option('ip_matcher_json', $json);

        // Показываем сообщение об ошибках, если есть
        if ($errors) {
            echo '<div class="notice notice-warning">Перечисленные значения не могут быть добавлены:<br>' . implode("<br>\n", $errors) . '</div>';
        }
    }
}
