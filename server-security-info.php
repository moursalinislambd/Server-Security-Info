<?php
/**
 * Plugin Name: Server & Security Info
 * Plugin URI: https://onexusdev.xyz
 * Description: 🔒 Lightweight server monitoring & security plugin. Shows server info, PHP config, API checker, user enumeration protection, directory indexing fix, and real-time monitor.
 * Version: 1.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Moursalin Islam
 * Author URI: https://onexusdev.xyz
 * License: GPL v3 or later
 * Text Domain: ssi
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// =============================================
// CONSTANTS
// =============================================

define('SSI_VERSION', '1.0');
define('SSI_URL', plugin_dir_url(__FILE__));
define('SSI_PATH', plugin_dir_path(__FILE__));

// Create log directory
$log_dir = SSI_PATH . 'logs';
if (!file_exists($log_dir)) {
    wp_mkdir_p($log_dir);
}

// =============================================
// ENQUEUE CSS AND JS
// =============================================

add_action('admin_enqueue_scripts', 'ssi_enqueue_assets');
function ssi_enqueue_assets($hook) {
    if (strpos($hook, 'ssi-dash') !== false) {
        wp_enqueue_style('ssi-style', SSI_URL . 'assets/ssi-style.css', array(), SSI_VERSION);
        wp_enqueue_script('ssi-script', SSI_URL . 'assets/ssi-script.js', array('jquery'), SSI_VERSION, true);
        wp_localize_script('ssi-script', 'ssi_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ssi_nonce')
        ));
    }
}

// =============================================
// ADMIN MENU
// =============================================

add_action('admin_menu', 'ssi_add_menu');
function ssi_add_menu() {
    add_menu_page(
        'Server Security',
        '🖥️ Server Info',
        'manage_options',
        'ssi-dash',
        'ssi_dashboard',
        'dashicons-chart-area',
        99
    );
}

// =============================================
// DASHBOARD
// =============================================

function ssi_dashboard() {
    $theme = isset($_COOKIE['ssi_theme']) ? $_COOKIE['ssi_theme'] : 'light';
    ?>
    <div class="ssi-wrap" data-theme="<?php echo esc_attr($theme); ?>">
        <div class="ssi-container">
            <div class="ssi-header">
                <div>
                    <h1>🛡️ Server & Security</h1>
                    <span class="ssi-badge">v<?php echo SSI_VERSION; ?></span>
                </div>
                <div>
                    <button class="ssi-btn" data-lang="en">🇬🇧 EN</button>
                    <button class="ssi-btn" data-lang="bn">🇧🇩 BN</button>
                    <button id="ssiThemeBtn" class="ssi-btn">🌓 Theme</button>
                    <button id="ssiRefreshBtn" class="ssi-btn">🔄 Refresh</button>
                </div>
            </div>
            <div class="ssi-grid">
                <?php 
                ssi_server_card(); 
                ssi_plugins_card(); 
                ssi_themes_card(); 
                ssi_php_card(); 
                ssi_storage_card(); 
                ssi_db_card(); 
                ssi_content_card(); 
                ssi_security_card(); 
                ssi_api_card(); 
                ssi_enum_card(); 
                ssi_dir_card(); 
                ssi_monitor_card(); 
                ssi_fix_card(); 
                ssi_developer_card(); 
                ?>
            </div>
        </div>
    </div>
    <?php
}

// =============================================
// SERVER CARD
// =============================================

function ssi_server_card() { 
    $load = function_exists('sys_getloadavg') ? sys_getloadavg() : array(0, 0, 0);
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🖥️</span>
            <h3>Server Info</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>PHP</span><span><?php echo phpversion(); ?></span></div>
            <div class="ssi-row"><span>MySQL</span><span><?php echo $GLOBALS['wpdb']->get_var("SELECT VERSION()"); ?></span></div>
            <div class="ssi-row"><span>WP</span><span><?php echo get_bloginfo('version'); ?></span></div>
            <div class="ssi-row"><span>CPU Load</span><span><?php echo round($load[0], 2); ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// PLUGINS CARD
// =============================================

function ssi_plugins_card() { 
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
    $active_plugins = count(get_option('active_plugins'));
    $total_plugins = count($all_plugins);
    $updates = get_site_transient('update_plugins');
    $update_count = !empty($updates->response) ? count($updates->response) : 0;
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🔌</span>
            <h3>Plugins</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>Total</span><span><?php echo $total_plugins; ?></span></div>
            <div class="ssi-row"><span>Active</span><span><?php echo $active_plugins; ?></span></div>
            <div class="ssi-row"><span>Updates</span><span class="<?php echo $update_count > 0 ? 'ssi-warning' : ''; ?>"><?php echo $update_count; ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// THEMES CARD
// =============================================

function ssi_themes_card() { 
    $current_theme = wp_get_theme(); 
    $all_themes = wp_get_themes(); 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🎨</span>
            <h3>Themes</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>Active</span><span><?php echo $current_theme->get('Name'); ?></span></div>
            <div class="ssi-row"><span>Version</span><span><?php echo $current_theme->get('Version'); ?></span></div>
            <div class="ssi-row"><span>Total</span><span><?php echo count($all_themes); ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// PHP CARD
// =============================================

function ssi_php_card() { ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>⚙️</span>
            <h3>PHP Config</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>max_execution</span><span><?php echo ini_get('max_execution_time'); ?>s</span></div>
            <div class="ssi-row"><span>memory_limit</span><span><?php echo ini_get('memory_limit'); ?></span></div>
            <div class="ssi-row"><span>upload_max</span><span><?php echo ini_get('upload_max_filesize'); ?></span></div>
            <div class="ssi-row"><span>post_max</span><span><?php echo ini_get('post_max_size'); ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// STORAGE CARD
// =============================================

function ssi_storage_card() { 
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'];
    if (file_exists($dir)) {
        $total = disk_total_space($dir);
        $free = disk_free_space($dir);
        $used = $total - $free;
        $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;
    } else {
        $total = 0;
        $free = 0;
        $used = 0;
        $percent = 0;
    }
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>💾</span>
            <h3>Storage</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-progress"><div style="width:<?php echo $percent; ?>%"></div></div>
            <div class="ssi-row"><span>Used</span><span><?php echo size_format($used, 2); ?> (<?php echo $percent; ?>%)</span></div>
            <div class="ssi-row"><span>Free</span><span><?php echo size_format($free, 2); ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// DATABASE CARD
// =============================================

function ssi_db_card() { 
    global $wpdb; 
    $size = $wpdb->get_var("SELECT SUM(data_length + index_length) FROM information_schema.tables WHERE table_schema = DATABASE()"); 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🗄️</span>
            <h3>Database</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>Size</span><span><?php echo size_format($size, 2); ?></span></div>
            <div class="ssi-row"><span>Tables</span><span><?php echo $wpdb->get_var("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"); ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// CONTENT CARD
// =============================================

function ssi_content_card() { 
    $users = count_users(); 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>📄</span>
            <h3>Content</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-stats">
                <div><span><?php echo wp_count_posts()->publish; ?></span><label>Posts</label></div>
                <div><span><?php echo wp_count_posts('page')->publish; ?></span><label>Pages</label></div>
                <div><span><?php echo wp_count_comments()->approved; ?></span><label>Comments</label></div>
                <div><span><?php echo $users['total_users']; ?></span><label>Users</label></div>
            </div>
        </div>
    </div>
    <?php 
}

// =============================================
// SECURITY CARD
// =============================================

function ssi_security_card() { 
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); 
    $debug = !defined('WP_DEBUG') || !WP_DEBUG; 
    $file_edit = defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT; 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🔒</span>
            <h3>Security</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row"><span>HTTPS</span><span class="<?php echo $https ? 'ssi-good' : 'ssi-bad'; ?>"><?php echo $https ? '✅ On' : '❌ Off'; ?></span></div>
            <div class="ssi-row"><span>WP_DEBUG</span><span class="<?php echo $debug ? 'ssi-good' : 'ssi-warning'; ?>"><?php echo $debug ? '✅ Off' : '⚠️ On'; ?></span></div>
            <div class="ssi-row"><span>File Editor</span><span class="<?php echo $file_edit ? 'ssi-good' : 'ssi-bad'; ?>"><?php echo $file_edit ? '✅ Disabled' : '❌ Enabled'; ?></span></div>
        </div>
    </div>
    <?php 
}

// =============================================
// API CHECK CARD
// =============================================

function ssi_api_card() { 
    $apis = array(
        'Posts' => '/wp-json/wp/v2/posts',
        'Pages' => '/wp-json/wp/v2/pages',
        'Users' => '/wp-json/wp/v2/users',
        'Comments' => '/wp-json/wp/v2/comments',
        'XML-RPC' => '/xmlrpc.php'
    ); 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🔌</span>
            <h3>API Check</h3>
        </div>
        <div class="ssi-card-body">
            <?php foreach($apis as $name => $endpoint): 
                $url = home_url($endpoint); 
                $resp = wp_remote_head($url, array('timeout' => 3, 'sslverify' => false)); 
                $code = !is_wp_error($resp) ? wp_remote_retrieve_response_code($resp) : 0; 
                $open = ($code >= 200 && $code < 400) || $code === 405; 
            ?>
            <div class="ssi-row">
                <span><?php echo $name; ?></span>
                <span class="<?php echo $open ? 'ssi-bad' : 'ssi-good'; ?>">
                    <?php echo $open ? "⚠️ Open($code)" : "✅ Secure($code)"; ?>
                </span>
            </div>
            <?php endforeach; ?>
            <div class="ssi-note">⚠️ Open APIs expose your data</div>
        </div>
    </div>
    <?php 
}

// =============================================
// USER ENUMERATION CARD
// =============================================

function ssi_enum_card() { 
    $vuln = false; 
    $users = array();
    
    for($id = 1; $id <= 10; $id++) {
        $url = home_url("/?author={$id}"); 
        $resp = wp_remote_head($url, array('timeout' => 3, 'redirection' => 0)); 
        if(!is_wp_error($resp)) {
            $loc = wp_remote_retrieve_header($resp, 'location'); 
            if($loc && preg_match('/author=([^&\/]+)/', $loc, $m)) {
                $vuln = true;
                $users[] = urldecode($m[1]);
            }
        }
    }
    
    $rest = wp_remote_get(home_url('/wp-json/wp/v2/users'), array('timeout' => 3)); 
    if(!is_wp_error($rest) && wp_remote_retrieve_response_code($rest) === 200) {
        $vuln = true;
        $data = json_decode(wp_remote_retrieve_body($rest), true); 
        if(!empty($data[0]['slug'])) {
            $users[] = $data[0]['slug'];
        }
    }
    $users = array_unique($users);
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>👥</span>
            <h3>User Enumeration</h3>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-row">
                <span>Status</span>
                <span class="<?php echo $vuln ? 'ssi-bad' : 'ssi-good'; ?>">
                    <?php echo $vuln ? '❌ Vulnerable' : '✅ Protected'; ?>
                </span>
            </div>
            <?php if($vuln && !empty($users)): ?>
            <div class="ssi-row">
                <span>Found</span>
                <span class="ssi-bad"><?php echo implode(', ', array_slice($users, 0, 5)); ?></span>
            </div>
            <?php endif; ?>
            <button class="ssi-fix-btn" data-fix="enum">🔧 Fix Enumeration</button>
            <div class="ssi-note">Prevents hackers finding usernames</div>
        </div>
    </div>
    <?php 
}

// =============================================
// DIRECTORY INDEXING CARD
// =============================================

function ssi_dir_card() { 
    $dirs = array(
        'Uploads' => '/wp-content/uploads/',
        'Plugins' => '/wp-content/plugins/',
        'Themes' => '/wp-content/themes/',
        'Admin' => '/wp-admin/',
        'Includes' => '/wp-includes/'
    ); 
    ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>📁</span>
            <h3>Directory Indexing</h3>
        </div>
        <div class="ssi-card-body">
            <?php foreach($dirs as $name => $path): 
                $url = home_url($path); 
                $resp = wp_remote_get($url, array('timeout' => 3)); 
                $body = !is_wp_error($resp) ? wp_remote_retrieve_body($resp) : ''; 
                $exposed = (strpos($body, 'Index of') !== false || strpos($body, 'Directory listing') !== false);
            ?>
            <div class="ssi-row">
                <span><?php echo $name; ?></span>
                <span class="<?php echo $exposed ? 'ssi-bad' : 'ssi-good'; ?>">
                    <?php echo $exposed ? '❌ Exposed' : '✅ Secure'; ?>
                </span>
            </div>
            <?php endforeach; ?>
            <button class="ssi-fix-btn" data-fix="dir">🔧 Fix Directory Listing</button>
            <div class="ssi-note">Prevents visitors seeing your file structure</div>
        </div>
    </div>
    <?php 
}

// =============================================
// LIVE MONITOR CARD
// =============================================

function ssi_monitor_card() { ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>📡</span>
            <h3>Live Monitor</h3>
            <span class="ssi-live">LIVE</span>
        </div>
        <div class="ssi-card-body">
            <div class="ssi-monitor">
                <div class="ssi-led" id="ssiLed"></div>
                <div>
                    <span id="ssiMsg">Checking...</span>
                    <small id="ssiTime">-</small>
                </div>
            </div>
            <div class="ssi-metrics">
                <div><label>Response</label><span id="ssiResp">-</span></div>
                <div><label>HTTP</label><span id="ssiHttp">-</span></div>
            </div>
            <button id="ssiCheckBtn" class="ssi-primary">🔍 Check Now</button>
            <label><input type="checkbox" id="ssiAuto" checked> Auto (30s)</label>
        </div>
    </div>
    <?php 
}

// =============================================
// QUICK FIXES CARD
// =============================================

function ssi_fix_card() { ?>
    <div class="ssi-card">
        <div class="ssi-card-title">
            <span>🔧</span>
            <h3>Quick Fixes</h3>
        </div>
        <div class="ssi-card-body">
            <button class="ssi-fix-btn" data-fix="htaccess">📝 Add Security to .htaccess</button>
            <button class="ssi-fix-btn" data-fix="wpconfig">⚙️ Secure wp-config.php</button>
            <button class="ssi-fix-btn" data-fix="cleanup">🗑️ Clean Unused Data</button>
            <div class="ssi-note">One-click security improvements</div>
        </div>
    </div>
    <?php 
}

// =============================================
// DEVELOPER CARD
// =============================================

function ssi_developer_card() { ?>
    <div class="ssi-card ssi-developer-card">
        <div class="ssi-card-title">
            <span>👨‍💻</span>
            <h3>Developer</h3>
        </div>
        <div class="ssi-card-body">
            <h4>Moursalin Islam</h4>
            <div>📞 +88096 47 882 445</div>
            <div>📞 +880134 67 52 141</div>
            <div>✉️ onexusdev@gmail.com</div>
            <div>🌐 <a href="https://onexusdev.xyz" target="_blank">onexusdev.xyz</a></div>
        </div>
    </div>
    <?php 
}

// =============================================
// AJAX HANDLERS
// =============================================

add_action('wp_ajax_ssi_check_site', 'ssi_ajax_check');
add_action('wp_ajax_ssi_apply_fix', 'ssi_apply_fix');

function ssi_ajax_check() {
    check_ajax_referer('ssi_nonce', 'nonce');
    $start = microtime(true);
    $resp = wp_remote_get(home_url(), array('timeout' => 5, 'sslverify' => false));
    $time = round((microtime(true) - $start) * 1000, 2);
    $code = !is_wp_error($resp) ? wp_remote_retrieve_response_code($resp) : 0;
    $up = !is_wp_error($resp) && $code >= 200 && $code < 400;
    wp_send_json_success(array(
        'status' => $up ? 'online' : 'offline',
        'response_time' => $time . 'ms',
        'http_code' => $code,
        'timestamp' => current_time('H:i:s')
    ));
}

function ssi_apply_fix() {
    check_ajax_referer('ssi_nonce', 'nonce');
    $fix = sanitize_text_field($_POST['fix_type']);
    $result = array('success' => false, 'message' => '');
    
    if ($fix == 'enum') {
        $htaccess = ABSPATH . '.htaccess';
        $rules = "\n# Block author enumeration\nRewriteCond %{QUERY_STRING} author=\\d [NC]\nRewriteRule .* - [R=403,L]\n";
        if (@file_put_contents($htaccess, $rules, FILE_APPEND)) {
            $result = array('success' => true, 'message' => 'Enumeration blocked!');
        } else {
            $result = array('success' => false, 'message' => 'Cannot write .htaccess');
        }
    } elseif ($fix == 'dir') {
        $htaccess = ABSPATH . '.htaccess';
        $rules = "\nOptions -Indexes\n";
        if (@file_put_contents($htaccess, $rules, FILE_APPEND)) {
            $result = array('success' => true, 'message' => 'Directory listing disabled!');
        } else {
            $result = array('success' => false, 'message' => 'Cannot write .htaccess');
        }
    } elseif ($fix == 'htaccess') {
        $htaccess = ABSPATH . '.htaccess';
        $rules = "\n# Security Rules\nOptions -Indexes\nServerSignature Off\n<FilesMatch \"\\.(htaccess|htpasswd|ini|log|sh|sql|bak)$\">\nOrder Allow,Deny\nDeny from all\n</FilesMatch>\n";
        if (@file_put_contents($htaccess, $rules, FILE_APPEND)) {
            $result = array('success' => true, 'message' => 'Security rules added!');
        } else {
            $result = array('success' => false, 'message' => 'Cannot write .htaccess');
        }
    } elseif ($fix == 'wpconfig') {
        $config = ABSPATH . 'wp-config.php';
        $content = "\n// Security fixes\ndefine('DISALLOW_FILE_EDIT', true);\ndefine('WP_DEBUG', false);\n";
        if (@file_put_contents($config, $content, FILE_APPEND)) {
            $result = array('success' => true, 'message' => 'wp-config.php secured!');
        } else {
            $result = array('success' => false, 'message' => 'Cannot write wp-config.php');
        }
    } elseif ($fix == 'cleanup') {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE post_type='revision'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}comments WHERE comment_approved='spam'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_%'");
        $result = array('success' => true, 'message' => 'Cleaned revisions, spam & transients!');
    }
    wp_send_json($result);
}
