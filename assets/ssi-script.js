jQuery(document).ready(function($){
    var interval = null;
    
    // Theme Toggle
    $('#ssiThemeBtn').on('click', function(){
        var wrap = $('.ssi-wrap');
        var current = wrap.attr('data-theme');
        var next = current === 'light' ? 'dark' : 'light';
        wrap.attr('data-theme', next);
        document.cookie = 'ssi_theme=' + next + '; path=/';
        $(this).html(next === 'light' ? '🌙 Dark' : '☀️ Light');
    });
    
    // Language Switch
    $('[data-lang]').on('click', function(){
        var lang = $(this).attr('data-lang');
        document.cookie = 'ssi_lang=' + lang + '; path=/';
        location.reload();
    });
    
    // Refresh Button
    $('#ssiRefreshBtn').on('click', function(){
        location.reload();
    });
    
    // Check Site Function
    function checkSite() {
        $('#ssiMsg').html('Checking...');
        $.ajax({
            url: ssi_ajax.url,
            type: 'POST',
            data: {
                action: 'ssi_check_site',
                nonce: ssi_ajax.nonce
            },
            success: function(response){
                if(response.success){
                    $('#ssiLed').attr('class', 'ssi-led ' + response.data.status);
                    $('#ssiMsg').html(response.data.status === 'online' ? '✅ Online' : '❌ Offline');
                    $('#ssiTime').html(response.data.timestamp);
                    $('#ssiResp').html(response.data.response_time);
                    $('#ssiHttp').html(response.data.http_code);
                }
            },
            error: function(){
                $('#ssiMsg').html('❌ Connection Error');
                $('#ssiLed').attr('class', 'ssi-led offline');
            }
        });
    }
    
    // Manual Check
    $('#ssiCheckBtn').on('click', function(){
        checkSite();
    });
    
    // Auto Refresh
    $('#ssiAuto').on('change', function(){
        if($(this).is(':checked')){
            interval = setInterval(checkSite, 30000);
        } else {
            if(interval) clearInterval(interval);
        }
    });
    
    // Start auto refresh if checked
    if($('#ssiAuto').is(':checked')){
        interval = setInterval(checkSite, 30000);
    }
    
    // Initial check
    checkSite();
    
    // Fix Buttons
    $('.ssi-fix-btn').on('click', function(){
        var btn = $(this);
        var fixType = btn.attr('data-fix');
        var originalText = btn.html();
        
        btn.html('⏳ Applying...');
        btn.prop('disabled', true);
        
        $.ajax({
            url: ssi_ajax.url,
            type: 'POST',
            data: {
                action: 'ssi_apply_fix',
                fix_type: fixType,
                nonce: ssi_ajax.nonce
            },
            success: function(response){
                if(response.success){
                    btn.html('✅ Fixed!');
                    setTimeout(function(){
                        btn.html(originalText);
                        btn.prop('disabled', false);
                        location.reload();
                    }, 1500);
                } else {
                    btn.html('❌ Failed');
                    setTimeout(function(){
                        btn.html(originalText);
                        btn.prop('disabled', false);
                    }, 2000);
                }
            },
            error: function(){
                btn.html('❌ Error');
                setTimeout(function(){
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }, 2000);
            }
        });
    });
});
