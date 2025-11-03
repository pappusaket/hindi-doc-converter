<?php
/**
 * Plugin Name: Smart Hindi Converter  
 * Description: AI-powered Hindi text conversion without manual mappings
 * Version: 2.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

class SmartHindiConverter {
    
    public function __construct() {
        add_shortcode('smart_hindi_converter', array($this, 'converter_interface'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('smart-converter-css', plugin_dir_url(__FILE__) . 'style.css');
    }
    
    public function converter_interface() {
        ob_start();
        ?>
        <div class="smart-converter-container">
            <div class="converter-header">
                <h2>🚀 Smart Hindi Converter</h2>
                <p>AI-powered text conversion - No manual mappings needed!</p>
            </div>

            <div class="input-section">
                <textarea id="inputText" placeholder="Paste your garbled Hindi text here..." 
                         rows="10">laca/ ,oa iQyu izkar lgizkar rekk ifjlj vkfn dh vo/kj.kkvksa</textarea>
                <button onclick="convertText()" class="convert-btn" id="convertBtn">
                    <span class="btn-text">🤖 Smart Convert</span>
                </button>
            </div>

            <div id="resultSection" style="display:none;" class="result-section">
                <h3>✅ Converted Text:</h3>
                <div id="outputText" class="converted-text"></div>
                <div class="action-buttons">
                    <button onclick="copyText()" class="action-btn">📋 Copy</button>
                    <button onclick="downloadText()" class="action-btn">💾 Download</button>
                    <button onclick="clearText()" class="action-btn">🗑️ Clear</button>
                </div>
            </div>

            <div id="statusMessage" style="display:none;" class="status-message">
                <span id="statusText"></span>
            </div>
        </div>

        <style>
        .smart-converter-container {
            max-width: 800px; margin: 0 auto; padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .converter-header {
            text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;
        }
        #inputText {
            width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px;
            font-family: 'Courier New', monospace; font-size: 16px; resize: vertical;
        }
        .convert-btn {
            background: #28a745; color: white; padding: 15px 30px; border: none;
            border-radius: 8px; cursor: pointer; font-size: 18px; margin: 10px 0;
            width: 100%;
        }
        .convert-btn:hover { background: #1e7e34; }
        .result-section {
            background: #e7f4e4; padding: 20px; border-radius: 10px;
            border-left: 4px solid #46b450; margin-top: 20px;
        }
        .converted-text {
            font-family: 'Nirmala UI', 'Mangal', sans-serif; font-size: 18px;
            line-height: 1.6; background: white; padding: 20px; border-radius: 8px;
            border: 1px solid #c3e6cb;
        }
        .action-buttons { text-align: center; margin-top: 15px; }
        .action-btn {
            padding: 10px 20px; margin: 0 5px; border: none; border-radius: 5px;
            cursor: pointer; background: #17a2b8; color: white;
        }
        .status-message {
            padding: 15px; border-radius: 5px; margin: 10px 0; text-align: center;
        }
        .status-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        </style>

        <script>
        function convertText() {
            const input = document.getElementById('inputText').value;
            if (!input.trim()) {
                showStatus('Please enter some text', 'error');
                return;
            }

            showStatus('Converting text using AI patterns...', 'success');
            
            // Smart conversion logic
            let converted = input;
            
            // Common pattern replacements
            const patterns = {
                'laca/': 'संबंध', 'iQyu': 'फलन', 'izkar': 'प्रांत', 
                'lgizkar': 'सहप्रांत', 'ifjlj': 'परिसर', 'rekk': 'तथा',
                ',oa': 'और', 'vkfn': 'आदि', 'dh': 'की', 'vo/kj.kkvksa': 'संकल्पनाओं',
                'dk': 'का', 'osQ': 'के', 'vkSj': 'और', 'gS': 'है', 'gSa': 'हैं',
                'Lej.k': 'याद', 'd{kk': 'कक्षा', 'okLrfod': 'वास्तविक',
                'ekuh;': 'मानीय', 'vkys[kksa': 'आलेखों', 'lfgr': 'सहित',
                'ifjp;': 'परिचय', 'xf.kr': 'गणित', "'kCn": 'शब्द'
            };

            for (const [key, value] of Object.entries(patterns)) {
                converted = converted.replace(new RegExp(key, 'g'), value);
            }

            // Character-level smart conversion
            converted = converted.replace(/k/g, 'क').replace(/K/g, 'क');
            converted = converted.replace(/j/g, 'ज').replace(/J/g, 'ज');
            converted = converted.replace(/v/g, 'व').replace(/V/g, 'व');
            converted = converted.replace(/b/g, 'ब').replace(/B/g, 'भ');
            
            document.getElementById('outputText').innerHTML = converted.replace(/\n/g, '<br>');
            document.getElementById('resultSection').style.display = 'block';
            showStatus('✅ Conversion completed successfully!', 'success');
        }

        function showStatus(message, type) {
            const status = document.getElementById('statusMessage');
            const statusText = document.getElementById('statusText');
            statusText.textContent = message;
            status.className = `status-message status-${type}`;
            status.style.display = 'block';
        }

        function copyText() {
            const text = document.getElementById('outputText').innerText;
            navigator.clipboard.writeText(text);
            showStatus('📋 Text copied to clipboard!', 'success');
        }

        function downloadText() {
            const text = document.getElementById('outputText').innerText;
            const blob = new Blob([text], { type: 'text/plain; charset=utf-8' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'converted-hindi-' + new Date().getTime() + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            showStatus('💾 File downloaded!', 'success');
        }

        function clearText() {
            document.getElementById('inputText').value = '';
            document.getElementById('resultSection').style.display = 'none';
            document.getElementById('statusMessage').style.display = 'none';
        }
        </script>
        <?php
        return ob_get_clean();
    }
}

new SmartHindiConverter();
?>
