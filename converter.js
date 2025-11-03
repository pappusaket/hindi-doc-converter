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
        'bl': 'इस',
        'osQ': 'के',
        'gS': 'है',
        'gSa': 'हैं',
        'fd': 'कि',
        'rekk': 'तथा',
        'vkfn': 'आदि',
        'dh': 'की',
        'dk': 'का',
        'ls': 'से',
        'ij': 'पर',
        'osQ': 'के',
        ',oa': 'और',
        'rks': 'तो',
        'vkSj': 'और',
        ';fn': 'यदि'
    };
    
    var converted = text;
    for (var garbled in fixes) {
        var regex = new RegExp(garbled, 'g');
        converted = converted.replace(regex, fixes[garbled]);
    }
    
    return converted.replace(/\n/g, '<br>');
}

// Utility functions
function copyToClipboard() {
    var text = document.getElementById('convertedContent').innerText;
    navigator.clipboard.writeText(text).then(function() {
        alert('Text copied to clipboard!');
    }).catch(function() {
        alert('Failed to copy text. Please select and copy manually.');
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
