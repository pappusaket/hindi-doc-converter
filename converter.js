jQuery(document).ready(function($) {
    // Show status function
    function showStatus(message, type = 'info') {
        $('#statusMessage').text(message);
        $('#statusBar').show().removeClass('status-success status-error').addClass('status-' + type);
        
        // Animate progress bar
        $('#progressFill').css('width', '0%');
        $('#progressFill').animate({ width: '100%' }, 1000);
    }
    
    // Hide status function
    function hideStatus() {
        $('#statusBar').hide();
    }
    
    // Show error function
    function showError(message) {
        $('#errorMessage').text(message);
        $('#errorSection').show();
        showStatus('Conversion failed', 'error');
    }
    
    // Hide error function
    function hideError() {
        $('#errorSection').hide();
    }
    
    // Show loading state for buttons
    function setButtonLoading(button, isLoading) {
        var btnText = button.find('.btn-text');
        var btnSpinner = button.find('.btn-spinner');
        
        if (isLoading) {
            btnText.hide();
            btnSpinner.show();
            button.prop('disabled', true);
        } else {
            btnText.show();
            btnSpinner.hide();
            button.prop('disabled', false);
        }
    }
    
    // File upload form handling
    $('#hindiUploadForm').on('submit', function(e) {
        e.preventDefault();
        
        var fileInput = $('#hindiFile')[0];
        if (fileInput.files.length === 0) {
            showError('Please select a file first.');
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'process_hindi_file');
        formData.append('nonce', hindi_converter_ajax.nonce);
        formData.append('hindi_file', fileInput.files[0]);
        
        // Show loading states
        showStatus('Uploading and processing file...', 'info');
        setButtonLoading($('#uploadConvertBtn'), true);
        $('#resultSection').hide();
        hideError();
        
        $.ajax({
            url: hindi_converter_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = (evt.loaded / evt.total) * 100;
                        $('#progressFill').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                setButtonLoading($('#uploadConvertBtn'), false);
                
                if (response.success) {
                    showStatus('Conversion completed successfully!', 'success');
                    $('#convertedContent').html(response.data);
                    $('#resultSection').show();
                    
                    // Auto-hide success status after 3 seconds
                    setTimeout(hideStatus, 3000);
                } else {
                    showError('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                setButtonLoading($('#uploadConvertBtn'), false);
                showError('Network error: ' + error);
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
        showError('Please enter some text to convert.');
        return;
    }
    
    // Show loading state
    showStatus('Converting text...', 'info');
    setButtonLoading($('#textConvertBtn'), true);
    hideError();
    
    // Simulate processing time for better UX
    setTimeout(function() {
        try {
            var converted = fixHindiText(text);
            document.getElementById('convertedContent').innerHTML = converted;
            document.getElementById('resultSection').style.display = 'block';
            showStatus('Text conversion completed!', 'success');
            setButtonLoading($('#textConvertBtn'), false);
            
            // Auto-hide success status after 3 seconds
            setTimeout(hideStatus, 3000);
        } catch (error) {
            showError('Conversion error: ' + error.message);
            setButtonLoading($('#textConvertBtn'), false);
        }
    }, 500);
}

// Global functions
window.showError = function(message) {
    $('#errorMessage').text(message);
    $('#errorSection').show();
}

window.hideError = function() {
    $('#errorSection').hide();
}

window.setButtonLoading = function(button, isLoading) {
    var btn = $(button);
    var btnText = btn.find('.btn-text');
    var btnSpinner = btn.find('.btn-spinner');
    
    if (isLoading) {
        btnText.hide();
        btnSpinner.show();
        btn.prop('disabled', true);
    } else {
        btnText.show();
        btnSpinner.hide();
        btn.prop('disabled', false);
    }
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
        ';fn': 'यदि',
        'vk': 'क',
        'mu': 'उन',
        'osQ': 'के',
        'vki': 'आप',
        'ge': 'हम',
        ';g': 'यह',
        'Hkh': 'भी',
        'ugha': 'नहीं',
        'gk¡': 'हाँ',
        'dks': 'को',
        'ds': 'के',
        'esa': 'में',
        'us': 'ने',
        'cjkcj': 'बराबर',
        'tks': 'जो',
        'rFkk': 'और',
        'vFkok': 'या',
        'ykxw': 'लागू',
        'gksrk': 'होता',
        'gksrs': 'होते',
        'gksrh': 'होती',
        'gks': 'हो',
        'dj': 'कर',
        'djrs': 'करते',
        'fd;k': 'किया',
        'tkrk': 'जाता',
        'tkrh': 'जाती',
        'tkrs': 'जाते',
        'ldrs': 'सकते',
        'ldrh': 'सकती',
        'ldrk': 'सकता'
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
        showStatus('Text copied to clipboard!', 'success');
        setTimeout(hideStatus, 2000);
    }).catch(function() {
        showError('Failed to copy text. Please select and copy manually.');
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
    showStatus('File downloaded successfully!', 'success');
    setTimeout(hideStatus, 2000);
}

function clearAll() {
    document.getElementById('directText').value = '';
    document.getElementById('hindiFile').value = '';
    document.getElementById('resultSection').style.display = 'none';
    hideError();
    hideStatus();
    $('.file-input-text').text('Choose TXT file');
}
