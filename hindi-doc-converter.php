<?php
/**
 * Plugin Name: Hindi DOC Converter
 * Plugin URI: https://github.com/yourusername/hindi-doc-converter
 * Description: Fix Unicode Hindi text issues in DOC files and convert to readable format
 * Version: 1.1.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 * Text Domain: hindi-doc-converter
 */

if (!defined('ABSPATH')) {
    exit;
}

class HindiDocConverter {
    
    public function __construct() {
        add_shortcode('hindi_converter', array($this, 'converter_interface'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_process_hindi_file', array($this, 'process_file'));
        add_action('wp_ajax_nopriv_process_hindi_file', array($this, 'process_file'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('hindi-converter-js', plugin_dir_url(__FILE__) . 'converter.js', array('jquery'), '1.1.0', true);
        wp_enqueue_style('hindi-converter-css', plugin_dir_url(__FILE__) . 'style.css', array(), '1.1.0');
        
        wp_localize_script('hindi-converter-js', 'hindi_converter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hindi_converter_nonce'),
            'processing_text' => __('Processing your file...', 'hindi-doc-converter'),
            'error_text' => __('Error processing file.', 'hindi-doc-converter'),
            'success_text' => __('Conversion completed successfully!', 'hindi-doc-converter')
        ));
    }
    
    public function converter_interface() {
        ob_start();
        ?>
        <div class="hindi-converter-container">
            <div class="converter-header">
                <h2><?php _e('Hindi DOC Converter', 'hindi-doc-converter'); ?></h2>
                <p><?php _e('Fix Unicode Hindi text issues in your documents', 'hindi-doc-converter'); ?></p>
            </div>
            
            <!-- Status Bar -->
            <div id="statusBar" class="status-bar" style="display: none;">
                <div class="status-content">
                    <span class="status-icon">⏳</span>
                    <span class="status-message" id="statusMessage">Processing...</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            
            <div class="upload-section">
                <h3><?php _e('Upload File', 'hindi-doc-converter'); ?></h3>
                <form id="hindiUploadForm" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" name="hindi_file" id="hindiFile" accept=".txt" required>
                        <label for="hindiFile" class="file-input-label">
                            <span class="file-input-text"><?php _e('Choose TXT file', 'hindi-doc-converter'); ?></span>
                            <span class="file-size-limit">(Max 5MB)</span>
                        </label>
                    </div>
                    <button type="submit" class="convert-btn" id="uploadConvertBtn">
                        <span class="btn-text"><?php _e('Convert File', 'hindi-doc-converter'); ?></span>
                        <span class="btn-spinner" style="display: none;">🔄</span>
                    </button>
                </form>
            </div>
            
            <div class="text-section">
                <h3><?php _e('Or Paste Text Directly', 'hindi-doc-converter'); ?></h3>
                <textarea id="directText" placeholder="<?php _e('Paste garbled Hindi text here...', 'hindi-doc-converter'); ?>"></textarea>
                <button type="button" onclick="convertDirectText()" class="convert-btn secondary" id="textConvertBtn">
                    <span class="btn-text"><?php _e('Convert Text', 'hindi-doc-converter'); ?></span>
                    <span class="btn-spinner" style="display: none;">🔄</span>
                </button>
            </div>
            
            <div id="resultSection" class="result-section" style="display: none;">
                <div class="result-header">
                    <h3><?php _e('Converted Content', 'hindi-doc-converter'); ?></h3>
                    <span class="success-badge">✅ Success</span>
                </div>
                <div id="convertedContent" class="converted-content"></div>
                <div class="action-buttons">
                    <button type="button" onclick="copyToClipboard()" class="action-btn copy-btn">
                        <?php _e('Copy Text', 'hindi-doc-converter'); ?>
                    </button>
                    <button type="button" onclick="downloadText()" class="action-btn download-btn">
                        <?php _e('Download', 'hindi-doc-converter'); ?>
                    </button>
                    <button type="button" onclick="clearAll()" class="action-btn clear-btn">
                        <?php _e('Clear All', 'hindi-doc-converter'); ?>
                    </button>
                </div>
            </div>
            
            <div id="errorSection" class="error-section" style="display: none;">
                <div class="error-header">
                    <h3>❌ Error</h3>
                </div>
                <div id="errorMessage" class="error-message"></div>
                <button type="button" onclick="hideError()" class="action-btn">OK</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function process_file() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'hindi_converter_nonce')) {
            wp_send_json_error('Security verification failed.');
        }
        
        if (!empty($_FILES['hindi_file'])) {
            $file = $_FILES['hindi_file'];
            
            // Check file type
            $allowed_types = array('text/plain');
            if (!in_array($file['type'], $allowed_types)) {
                wp_send_json_error('Please upload text files (.txt) only. DOC support coming soon.');
            }
            
            // Check file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                wp_send_json_error('File size too large. Maximum 5MB allowed.');
            }
            
            // Check if file is empty
            if ($file['size'] == 0) {
                wp_send_json_error('File is empty. Please upload a valid text file.');
            }
            
            // Process the file
            $content = file_get_contents($file['tmp_name']);
            
            // Check if file content is readable
            if ($content === false) {
                wp_send_json_error('Unable to read file content.');
            }
            
            $converted_content = $this->convert_hindi_text($content);
            
            wp_send_json_success($converted_content);
        }
        
        wp_send_json_error('No file uploaded.');
    }
    
    private function convert_hindi_text($text) {
        $hindi_fixes = array(
            // Complete mapping list
            'laca/' => 'संबंध',
            'iQyu' => 'फलन',
            'izkar' => 'प्रांत',
            'lgizkar' => 'सहप्रांत',
            'ifjlj' => 'परिसर',
            'vo/kj.kkvksa' => 'संकल्पनाओं',
            'Lej.k' => 'याद',
            'd{kk' => 'कक्षा',
            'okLrfod' => 'वास्तविक',
            'ekuh;' => 'मानीय',
            'vkys[kksa' => 'आलेखों',
            'lfgr' => 'सहित',
            'ifjp;' => 'परिचय',
            'xf.kr' => 'गणित',
            "'kCn" => 'शब्द',
            "laca/" => 'संबंध',
            "loaQYiuk" => 'संकल्पना',
            "vaxzs”kh" => 'अंग्रेजी',
            "Hkkekk" => 'भाषा',
            "vekZ" => 'अर्थ',
            "vuqlkj" => 'अनुसार',
            "oLrq,¡" => 'वस्तुएँ',
            "ijLij" => 'परस्पर',
            "lacaf/r" => 'संबंधित',
            "vfHkKs;" => 'पहचान योग्य',
            "dM+h" => 'कड़ी',
            "LowQy" => 'विद्यालय',
            "fo|kfekZ;ksa" => 'विद्यार्थियों',
            "leqPp;" => 'समुच्चय',
            "mnkgj.k" => 'उदाहरण',
            'fofHkUu' => 'विभिन्न',
            'izdkj' => 'प्रकार',
            ',oa' => 'और',
            'rekk' => 'तथा',
            'vkfn' => 'आदि',
            'dk' => 'का',
            'osQ' => 'के',
            'vkSj' => 'और',
            'djk;k' => 'दिया',
            'ksa' => 'ों',
            'tk' => 'जा',
            'pqdk' => 'चुका',
            'gS' => 'है',
            'gSa' => 'हैं',
            'lg' => 'सह',
            'bl' => 'इस',
            'fd' => 'कि',
            'dh' => 'की',
            'ls' => 'से',
            'ij' => 'पर',
            'rks' => 'तो',
            ';fn' => 'यदि',
            'vk' => 'क',
            'mu' => 'उन',
            'osQ' => 'के',
            'vki' => 'आप',
            'ge' => 'हम',
            ';g' => 'यह',
            'Hkh' => 'भी',
            'ugha' => 'नहीं',
            'gk¡' => 'हाँ',
            'dks' => 'को',
            'ds' => 'के',
            'esa' => 'में',
            'us' => 'ने',
            'cjkcj' => 'बराबर',
            'tks' => 'जो',
            'rFkk' => 'और',
            'vFkok' => 'या',
            'ykxw' => 'लागू',
            'gksrk' => 'होता',
            'gksrs' => 'होते',
            'gksrh' => 'होती',
            'gks' => 'हो',
            'dj' => 'कर',
            'djrs' => 'करते',
            'fd;k' => 'किया',
            'tkrk' => 'जाता',
            'tkrh' => 'जाती',
            'tkrs' => 'जाते',
            'ldrs' => 'सकते',
            'ldrh' => 'सकती',
            'ldrk' => 'सकता'
        );
        
        foreach ($hindi_fixes as $garbled => $proper) {
            $text = str_replace($garbled, $proper, $text);
        }
        
        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}

// Initialize the plugin
new HindiDocConverter();
?>
