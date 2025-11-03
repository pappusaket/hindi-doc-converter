<?php
/**
 * Plugin Name: Hindi DOC Converter
 * Plugin URI: https://github.com/yourusername/hindi-doc-converter
 * Description: Fix Unicode Hindi text issues in DOC files and convert to readable format
 * Version: 1.0.1
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
        wp_enqueue_script('hindi-converter-js', plugin_dir_url(__FILE__) . 'converter.js', array('jquery'), '1.0.1', true);
        wp_enqueue_style('hindi-converter-css', plugin_dir_url(__FILE__) . 'style.css', array(), '1.0.1');
        
        wp_localize_script('hindi-converter-js', 'hindi_converter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hindi_converter_nonce'),
            'processing_text' => __('Processing your file...', 'hindi-doc-converter'),
            'error_text' => __('Error processing file.', 'hindi-doc-converter')
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
            
            <div class="upload-section">
                <h3><?php _e('Upload File', 'hindi-doc-converter'); ?></h3>
                <form id="hindiUploadForm" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" name="hindi_file" id="hindiFile" accept=".doc,.docx,.txt" required>
                        <label for="hindiFile" class="file-input-label">
                            <span class="file-input-text"><?php _e('Choose DOC/DOCX/TXT file', 'hindi-doc-converter'); ?></span>
                        </label>
                    </div>
                    <button type="submit" class="convert-btn">
                        <?php _e('Convert File', 'hindi-doc-converter'); ?>
                    </button>
                </form>
            </div>
            
            <div class="text-section">
                <h3><?php _e('Or Paste Text Directly', 'hindi-doc-converter'); ?></h3>
                <textarea id="directText" placeholder="<?php _e('Paste garbled Hindi text here...', 'hindi-doc-converter'); ?>"></textarea>
                <button type="button" onclick="convertDirectText()" class="convert-btn secondary">
                    <?php _e('Convert Text', 'hindi-doc-converter'); ?>
                </button>
            </div>
            
            <div id="resultSection" class="result-section">
                <h3><?php _e('Converted Content', 'hindi-doc-converter'); ?></h3>
                <div id="convertedContent" class="converted-content"></div>
                <div class="action-buttons">
                    <button type="button" onclick="copyToClipboard()" class="action-btn copy-btn">
                        <?php _e('Copy Text', 'hindi-doc-converter'); ?>
                    </button>
                    <button type="button" onclick="downloadText()" class="action-btn download-btn">
                        <?php _e('Download', 'hindi-doc-converter'); ?>
                    </button>
                </div>
            </div>
            
            <div id="loadingSpinner" class="loading-spinner">
                <p><?php _e('Processing your file... Please wait.', 'hindi-doc-converter'); ?></p>
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
            
            // Process the file
            $content = file_get_contents($file['tmp_name']);
            $converted_content = $this->convert_hindi_text($content);
            
            wp_send_json_success($converted_content);
        }
        
        wp_send_json_error('No file uploaded.');
    }
    
    private function convert_hindi_text($text) {
        $hindi_fixes = array(
            // Basic words
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
            
            // NEW COMPREHENSIVE MAPPINGS
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
            'dh' => 'की',
            'ls' => 'से',
            'ij' => 'पर',
            'rks' => 'तो',
            ';fn' => 'यदि',
            'bl' => 'इस',
            'mlh' => 'उसी',
            'fd' => 'कि',
            'fdlh' => 'किसी',
            'osQ' => 'के',
            'muosQ' => 'उनके',
            'bldk' => 'इसका',
            'rqY;rk' => 'तुल्यता',
            'rqY;' => 'तुल्य',
            'LorqY;' => 'स्वतुल्य',
            'lefer' => 'सममित',
            'laØked' => 'संक्रामक',
            'rqY;rk laca/' => 'तुल्यता संबंध',
            'rqY;rk oxZ' => 'तुल्यता वर्ग',
            'mileqPp;' => 'उपसमुच्चय',
            'laiw.kZ' => 'संपूर्ण',
            'vfLrRo' => 'अस्तित्व',
            'izekf.kr' => 'प्रमाणित',
            'fl¼' => 'सिद्ध',
            'dgrs' => 'कहते',
            'dgk' => 'कहा',
            'dgk tkrk' => 'कहा जाता',
            'tSlk' => 'जैसा',
            'tSls' => 'जैसे',
            'mnkgj.k' => 'उदाहरण',
            'fopkj' => 'विचार',
            'nhft,' => 'दीजिए',
            'dhft,' => 'कीजिए',
            'crykb,' => 'बताइए',
            'Kkr' => 'ज्ञात',
            'O;qRØe.kh;' => 'प्रतिलोमी',
            'izkIr' => 'प्राप्त',
            'fuvfir' => 'निरूपित',
            'izrhd' => 'प्रतीक',
            'ifjHkkf"kr' => 'परिभाषित',
            'losZ{k.k' => 'प्रेक्षण',
            'izek.k' => 'प्रमाण',
            'ladYiuk' => 'संकल्पना',
            'egÙoiw.kZ' => 'महत्वपूर्ण',
            'vR;Ur' => 'अत्यंत',
            'lkekU;r%' => 'सामान्यतः',
            'vr%' => 'अतः',
            'blfy,' => 'इसलिए',
            'blosQ' => 'इसके',
            'bls' => 'इसे',
            'buls' => 'इनसे',
            'os' => 'वे',
            ';g' => 'यह',
            'og' => 'वह',
            'dksbZ' => 'कोई',
            'dHkh' => 'कभी',
            'lnSo' => 'सदैव',
            'okLro' => 'वास्तव',
            'okLro esa' => 'वास्तव में',
            'laHkor%' => 'संभवतः',
            'vko';d' => 'आवश्यक',
            'vfuok;Zr%' => 'अनिवार्यतः',
            'vU;Fkk' => 'अन्यथा',
            'oSQs' => 'ऐसा',
            'oSQls' => 'कैसे',
            'fdruk' => 'कितना',
            'fdrus' => 'कितने',
            'tgk¡' => 'जहाँ',
            'rc' => 'तब',
            'rFkk' => 'तथा',
            'vFkok' => 'या',
            'ijUrq' => 'परंतु',
            'fQj' => 'फिर',
            'vFkZr~' => 'अर्थात्',
            'uker%' => 'नामतः',
            'eku yhft,' => 'मान लीजिए',
            ';fn vkSj osQoy ;fn' => 'यदि और केवल यदि',
            'ftlesa' => 'जिसमें',
            'ftldk' => 'जिसका',
            'ftlosQ' => 'जिसके',
            'ftlls' => 'जिससे',
            'tks' => 'जो',
            'mUgha' => 'उन्हीं',
            'mldk' => 'उसका',
            'mlosQ' => 'उसके',
            'mls' => 'उसे',
            'mu' => 'उन',
            'muesa' => 'उनमें',
            'mUgsa' => 'उन्हें',
            'ge' => 'हम',
            'gesa' => 'हमें',
            'vki' => 'आप',
            'vkidks' => 'आपको',
            'eq>s' => 'मुझे',
            'eSa' => 'मैं',
            'rqe' => 'तुम',
            'osQoy' => 'केवल',
            'lkFk' => 'साथ',
            'ckn' => 'बाद',
            'igys' => 'पहले',
            'vkt' => 'आज',
            'vHkh' => 'अभी',
            'dHkh&dHkh' => 'कभी-कभी',
            'lnk' => 'हमेशा',
            'vPNk' => 'अच्छा',
            'cM+k' => 'बड़ा',
            'NksVk' => 'छोटा',
            'vf/d' => 'अधिक',
            'de' => 'कम',
            'cgqr' => 'बहुत',
            'cgq' => 'बहु',
            'vf}rh;' => 'अद्वितीय',
            'vfrfjDr' => 'अतिरिक्त',
            'vU;' => 'अन्य',
            'leku' => 'समान',
            'fHkUu' => 'भिन्न',
            'vyx' => 'अलग',
            'leLr' => 'समस्त',
            'lHkh' => 'सभी',
            'izR;sd' => 'प्रत्येक',
            'dksbZ Hkh' => 'कोई भी',
            'dHkh Hkh' => 'कभी भी',
            'fdlh Hkh' => 'किसी भी',
            ';gka' => 'यहाँ',
            'ogka' => 'वहाँ',
            'fdlh' => 'किसी',
            'fdlh dks' => 'किसी को',
            'fdlh ls' => 'किसी से',
            'fdlh dk' => 'किसी का',
            'fdlh osQ' => 'किसी के',
            'fdlh ij' => 'किसी पर',
            'fdlh rd' => 'किसी तक',
            'fdlh ds' => 'किसी के',
            'fdlh us' => 'किसी ने',
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
