jQuery(document).ready(function($) {
    // File upload form handling
    $('#hindiUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#hindiFile')[0];
        if (fileInput.files.length === 0) {
            alert('Please select a file first.');
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'process_hindi_file');
        formData.append('nonce', hindi_converter_ajax.nonce);
        formData.append('hindi_file', fileInput.files[0]);
        
        // Show loading, hide results
        $('#loadingSpinner').show();
        $('#resultSection').hide();
        
        $.ajax({
            url: hindi_converter_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#loadingSpinner').hide();
                if (response.success) {
                    $('#convertedContent').html(response.data);
                    $('#resultSection').show();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                $('#loadingSpinner').hide();
                alert(hindi_converter_ajax.error_text);
            }
        });
    });
    
    // File input styling
    $('#hindiFile').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).siblings('.file-input-label').find('.file-input-text').text(fileName);
        }
    });
});

// Direct text conversion
function convertDirectText() {
    var text = document.getElementById('directText').value;
    if (!text.trim()) {
        alert('Please enter some text to convert.');
        return;
    }
    
    var converted = fixHindiText(text);
    document.getElementById('convertedContent').innerHTML = converted;
    document.getElementById('resultSection').style.display = 'block';
}

// Hindi text conversion function
function fixHindiText(text) {
    var fixes = {
        // Basic words
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
        
        // COMPREHENSIVE MAPPINGS
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
        'dh': 'की',
        'ls': 'से',
        'ij': 'पर',
        'rks': 'तो',
        ';fn': 'यदि',
        'bl': 'इस',
        'mlh': 'उसी',
        'fd': 'कि',
        'fdlh': 'किसी',
        'osQ': 'के',
        'muosQ': 'उनके',
        'bldk': 'इसका',
        'rqY;rk': 'तुल्यता',
        'rqY;': 'तुल्य',
        'LorqY;': 'स्वतुल्य',
        'lefer': 'सममित',
        'laØked': 'संक्रामक',
        'rqY;rk laca/': 'तुल्यता संबंध',
        'rqY;rk oxZ': 'तुल्यता वर्ग',
        'mileqPp;': 'उपसमुच्चय',
        'la
