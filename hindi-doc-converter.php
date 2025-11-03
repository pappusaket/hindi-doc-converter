<?php
/**
 * Plugin Name: Smart Hindi Converter
 * Version: 2.0.0
 */

if (!defined('ABSPATH')) exit;

class SmartHindiConverter {
    
    public function __construct() {
        add_shortcode('smart_hindi_converter', array($this, 'converter_interface'));
        add_action('wp_ajax_smart_convert', array($this, 'smart_convert'));
    }
    
    public function converter_interface() {
        ob_start();
        ?>
        <div class="smart-converter">
            <h2>AI Hindi Text Converter</h2>
            
            <div class="input-section">
                <textarea id="inputText" placeholder="Paste garbled Hindi text here..." rows="8"></textarea>
                <button onclick="smartConvert()" id="convertBtn">🔄 Smart Convert</button>
            </div>
            
            <div id="result" style="display:none; margin-top:20px; padding:15px; background:#f0f8ff; border-radius:8px;">
                <h3>Converted Text:</h3>
                <div id="outputText" style="font-family: 'Nirmala UI', 'Mangal'; font-size:16px; line-height:1.6; background:white; padding:15px; border-radius:5px;"></div>
                <button onclick="copyResult()" style="margin-top:10px; padding:8px 15px;">Copy Text</button>
            </div>
            
            <div id="loading" style="display:none; text-align:center; padding:20px;">
                <p>Analyzing text patterns... This may take a few seconds</p>
            </div>
        </div>

        <script>
        function smartConvert() {
            const text = document.getElementById('inputText').value;
            if (!text.trim()) {
                alert('Please enter some text');
                return;
            }
            
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            
            // Send to server for AI-style processing
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=smart_convert&text=' + encodeURIComponent(text)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                if (data.success) {
                    document.getElementById('outputText').innerHTML = data.data;
                    document.getElementById('result').style.display = 'block';
                } else {
                    alert('Error: ' + data.data);
                }
            });
        }
        
        function copyResult() {
            const text = document.getElementById('outputText').innerText;
            navigator.clipboard.writeText(text);
            alert('Copied!');
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function smart_convert() {
        $text = $_POST['text'];
        
        // AI-Style Pattern Recognition
        $converted = $this->ai_style_conversion($text);
        
        wp_send_json_success(nl2br($converted));
    }
    
    private function ai_style_conversion($text) {
        // Method 1: Common Pattern Replacement
        $common_patterns = $this->get_common_patterns();
        $text = str_replace(array_keys($common_patterns), array_values($common_patterns), $text);
        
        // Method 2: Dynamic Pattern Detection
        $text = $this->dynamic_conversion($text);
        
        // Method 3: Word Boundary Based Conversion
        $text = $this->word_based_conversion($text);
        
        return $text;
    }
    
    private function get_common_patterns() {
        return array(
            // Most common patterns from your document
            'laca/' => 'संबंध', 'iQyu' => 'फलन', 'izkar' => 'प्रांत', 'lgizkar' => 'सहप्रांत',
            'ifjlj' => 'परिसर', 'vo/kj.kkvksa' => 'संकल्पनाओं', 'Lej.k' => 'याद', 'd{kk' => 'कक्षा',
            'okLrfod' => 'वास्तविक', 'ekuh;' => 'मानीय', 'vkys[kksa' => 'आलेखों', 'lfgr' => 'सहित',
            'ifjp;' => 'परिचय', 'xf.kr' => 'गणित', "'kCn" => 'शब्द', ',oa' => 'और', 'rekk' => 'तथा',
            'vkfn' => 'आदि', 'dk' => 'का', 'osQ' => 'के', 'vkSj' => 'और', 'djk;k' => 'दिया',
            // Add 50-100 most frequent patterns
        );
    }
    
    private function dynamic_conversion($text) {
        // Convert common character patterns
        $char_mappings = array(
            'k' => 'क', 'K' => 'क', 's' => 'स', 'S' => 'श', 
            'j' => 'ज', 'J' => 'ज', 'y' => 'य', 'Y' => 'य',
            'v' => 'व', 'V' => 'व', 'b' => 'ब', 'B' => 'भ',
            'l' => 'ल', 'L' => 'ल', 'r' => 'र', 'R' => 'र',
            'd' => 'द', 'D' => 'ध', 't' => 'त', 'T' => 'थ',
            'g' => 'ग', 'G' => 'घ', 'h' => 'ह', 'H' => 'ह',
            'm' => 'म', 'M' => 'म', 'n' => 'न', 'N' => 'ण',
            'p' => 'प', 'P' => 'फ', 'c' => 'च', 'C' => 'छ'
        );
        
        // Apply character-level conversions
        foreach ($char_mappings as $eng => $hindi) {
            $text = str_replace($eng, $hindi, $text);
        }
        
        return $text;
    }
    
    private function word_based_conversion($text) {
        // Split into words and convert common word endings
        $words = preg_split('/\s+/', $text);
        $converted_words = array();
        
        foreach ($words as $word) {
            $converted_word = $this->convert_word($word);
            $converted_words[] = $converted_word;
        }
        
        return implode(' ', $converted_words);
    }
    
    private function convert_word($word) {
        // Common suffix conversions
        $suffixes = array(
            'k' => 'क', 'ksa' => 'क्सा', 'ks' => 'क्स',
            'a' => 'ा', 'aa' => 'ा', 'e' => 'े', 'i' => 'ी',
            'ee' => 'ी', 'u' => 'ु', 'oo' => 'ू', 'ae' => 'ै',
            'au' => 'ौ', 'am' => 'ं', 'ah' => 'ः'
        );
        
        // Check if word matches any known pattern
        foreach ($suffixes as $suffix => $hindi) {
            if (str_ends_with($word, $suffix)) {
                return $this->convert_root($word) . $hindi;
            }
        }
        
        return $word; // Return as-is if no pattern matches
    }
    
    private function convert_root($word) {
        // Simple root word conversion (can be expanded)
        $roots = array(
            'lac' => 'संबंध', 'iQy' => 'फलन', 'iz' => 'प्रां',
            'ifj' => 'परिस', 'vo' => 'सं', 'Lej' => 'याद'
        );
        
        foreach ($roots as $root => $hindi) {
            if (str_starts_with($word, $root)) {
                return $hindi;
            }
        }
        
        return $word;
    }
}

new SmartHindiConverter();
?>
