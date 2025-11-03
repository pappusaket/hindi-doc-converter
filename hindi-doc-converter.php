<?php
/**
 * Plugin Name: Hindi DOC Converter
 * Plugin URI: https://github.com/yourusername/hindi-doc-converter
 * Description: Fix Unicode Hindi text issues in DOC files and convert to readable format
 * Version: 1.2.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 * Text Domain: hindi-doc-converter
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if class already exists to prevent conflicts
if (!class_exists('HindiDocConverter')) {

class HindiDocConverter {
    
    public function __construct() {
        add_shortcode('hindi_converter', array($this, 'converter_interface'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_process_hindi_file', array($this, 'process_file'));
        add_action('wp_ajax_nopriv_process_hindi_file', array($this, 'process_file'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('hindi-converter-js', plugin_dir_url(__FILE__) . 'converter.js', array('jquery'), '1.2.0', true);
        wp_enqueue_style('hindi-converter-css', plugin_dir_url(__FILE__) . 'style.css', array(), '1.2.0');
        
        wp_localize_script('hindi-converter-js', 'hindi_converter_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hindi_converter_nonce'),
            'processing_text' => 'Processing your file...',
            'error_text' => 'Error processing file.',
            'success_text' => 'Conversion completed successfully!'
        ));
    }
    
    public function converter_interface() {
        ob_start();
        ?>
        <div class="hindi-converter-container">
            <div class="converter-header">
                <h2>Hindi DOC Converter</h2>
                <p>Fix Unicode Hindi text issues in your documents</p>
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
                <h3>Upload File</h3>
                <form id="hindiUploadForm" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" name="hindi_file" id="hindiFile" accept=".txt" required>
                        <label for="hindiFile" class="file-input-label">
                            <span class="file-input-text">Choose TXT file</span>
                            <span class="file-size-limit">(Max 5MB)</span>
                        </label>
                    </div>
                    <button type="submit" class="convert-btn" id="uploadConvertBtn">
                        <span class="btn-text">Convert File</span>
                        <span class="btn-spinner" style="display: none;">🔄</span>
                    </button>
                </form>
            </div>
            
            <div class="text-section">
                <h3>Or Paste Text Directly</h3>
                <textarea id="directText" placeholder="Paste garbled Hindi text here..."></textarea>
                <button type="button" onclick="convertDirectText()" class="convert-btn secondary" id="textConvertBtn">
                    <span class="btn-text">Convert Text</span>
                    <span class="btn-spinner" style="display: none;">🔄</span>
                </button>
            </div>
            
            <div id="resultSection" class="result-section" style="display: none;">
                <div class="result-header">
                    <h3>Converted Content</h3>
                    <span class="success-badge">✅ Success</span>
                </div>
                <div id="convertedContent" class="converted-content"></div>
                <div class="action-buttons">
                    <button type="button" onclick="copyToClipboard()" class="action-btn copy-btn">
                        Copy Text
                    </button>
                    <button type="button" onclick="downloadText()" class="action-btn download-btn">
                        Download
                    </button>
                    <button type="button" onclick="clearAll()" class="action-btn clear-btn">
                        Clear All
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

        <script>
        function convertDirectText() {
            var text = document.getElementById('directText').value;
            if (!text.trim()) {
                alert('Please enter some text to convert.');
                return;
            }
            
            // Show loading state
            document.getElementById('statusMessage').textContent = 'Converting text...';
            document.getElementById('statusBar').style.display = 'block';
            document.getElementById('statusBar').className = 'status-bar status-info';
            
            var btn = document.getElementById('textConvertBtn');
            var btnText = btn.querySelector('.btn-text');
            var btnSpinner = btn.querySelector('.btn-spinner');
            
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline';
            btn.disabled = true;
            
            // Progress animation
            var progressFill = document.getElementById('progressFill');
            progressFill.style.width = '0%';
            setTimeout(function() {
                progressFill.style.width = '100%';
            }, 100);
            
            // Simulate processing
            setTimeout(function() {
                try {
                    var converted = fixHindiText(text);
                    document.getElementById('convertedContent').innerHTML = converted;
                    document.getElementById('resultSection').style.display = 'block';
                    
                    // Show success
                    document.getElementById('statusMessage').textContent = 'Text conversion completed!';
                    document.getElementById('statusBar').className = 'status-bar status-success';
                    
                    // Reset button
                    btnText.style.display = 'inline';
                    btnSpinner.style.display = 'none';
                    btn.disabled = false;
                    
                    // Auto-hide success after 3 seconds
                    setTimeout(function() {
                        document.getElementById('statusBar').style.display = 'none';
                    }, 3000);
                    
                } catch (error) {
                    showError('Conversion error: ' + error.message);
                    btnText.style.display = 'inline';
                    btnSpinner.style.display = 'none';
                    btn.disabled = false;
                }
            }, 1000);
        }

        function fixHindiText(text) {
            var fixes = {
                'laca/': 'संबंध',
                'iQyu': 'फलन',
                'izkar': 'प्रांत',
                'lgizkar': 'सहप्रांत',
                'ifjlj': 'परिसर',
                'vo/kj.kkvksa': 'संकल्पनाओं',
                'Lej.k': 'याद',
                'd{kk': 'कक्षा',
                'okLrfod': 'वास्तविक',
                'ekuh;': 'मानीय',
                'vkys[kksa': 'आलेखों',
                'lfgr': 'सहित',
                'ifjp;': 'परिचय',
                'xf.kr': 'गणित',
                "'kCn": 'शब्द',
                "laca/": 'संबंध',
                "loaQYiuk": 'संकल्पना',
                "vaxzs”kh": 'अंग्रेजी',
                "Hkkekk": 'भाषा',
                "vekZ": 'अर्थ',
                "vuqlkj": 'अनुसार',
                "oLrq,¡": 'वस्तुएँ',
                "ijLij": 'परस्पर',
                "lacaf/r": 'संबंधित',
                "vfHkKs;": 'पहचान योग्य',
                "dM+h": 'कड़ी',
                "LowQy": 'विद्यालय',
                "fo|kfekZ;ksa": 'विद्यार्थियों',
                "leqPp;": 'समुच्चय',
                "mnkgj.k": 'उदाहरण',
                'fofHkUu': 'विभिन्न',
                'izdkj': 'प्रकार',
                ',oa': 'और',
                'rekk': 'तथा',
                'vkfn': 'आदि',
                'dk': 'का',
                'osQ': 'के',
                'vkSj': 'और',
                'djk;k': 'दिया',
                'ksa': 'ों',
                'tk': 'जा',
                'pqdk': 'चुका',
                'gS': 'है',
                'gSa': 'हैं',
                'lg': 'सह',
                'bl': 'इस',
                'fd': 'कि',
                'dh': 'की',
                'ls': 'से',
                'ij': 'पर',
                'rks': 'तो',
                ';fn': 'यदि'
            };
            
            var converted = text;
            for (var garbled in fixes) {
                var regex = new RegExp(garbled, 'g');
                converted = converted.replace(regex, fixes[garbled]);
            }
            
            return converted.replace(/\n/g, '<br>');
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorSection').style.display = 'block';
        }

        function hideError() {
            document.getElementById('errorSection').style.display = 'none';
        }

        function copyToClipboard() {
            var text = document.getElementById('convertedContent').innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('Text copied to clipboard!');
            });
        }

        function downloadText() {
            var text = document.getElementById('convertedContent').innerText;
            var blob = new Blob([text], { type: 'text/plain; charset=utf-8' });
            var url = window.URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'converted-hindi-text-' + new Date().getTime() + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        function clearAll() {
            document.getElementById('directText').value = '';
            document.getElementById('hindiFile').value = '';
            document.getElementById('resultSection').style.display = 'none';
            document.getElementById('errorSection').style.display = 'none';
            document.getElementById('statusBar').style.display = 'none';
            document.querySelector('.file-input-text').textContent = 'Choose TXT file';
        }
        </script>
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
            'gSa' => 'हैं'
        );
        
        foreach ($hindi_fixes as $garbled => $proper) {
            $text = str_replace($garbled, $proper, $text);
        }
        
        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}

new HindiDocConverter();

} // End of class_exists check
?>
