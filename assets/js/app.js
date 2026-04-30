/**
 * Allekirjoituspalvelu — app.js  v2.0
 * jQuery 3.7.1 — UI interactions, animations, upload dropzone, signer management
 */
(function ($) {
    'use strict';

    /* ── DOMReady ──────────────────────────────────────────────── */
    $(function () {
        initAnimations();
        initSidebar();
        initDropzone();
        initSignerRows();
        initFlashAutoDismiss();
        initConfirmForms();
        initUploadForm();
    });

    /* ── Entrance animations ──────────────────────────────────── */
    function initAnimations() {
        if (!window.IntersectionObserver) {
            $('[data-animate]').addClass('animated');
            return;
        }
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var $el    = $(entry.target);
                    var delay  = parseInt($el.attr('data-delay') || '0', 10);
                    setTimeout(function () { $el.addClass('animated'); }, delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        $('[data-animate]').each(function () { observer.observe(this); });
    }

    /* ── Mobile sidebar ───────────────────────────────────────── */
    function initSidebar() {
        var $sidebar = $('#sidebar');
        var $overlay = $('#sidebarOverlay');
        var $toggle  = $('#sidebarToggle');

        $toggle.on('click', function () {
            var open = $sidebar.hasClass('open');
            $sidebar.toggleClass('open', !open);
            $overlay.toggleClass('open', !open);
        });
        $overlay.on('click', closeSidebar);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });

        function closeSidebar() {
            $sidebar.removeClass('open');
            $overlay.removeClass('open');
        }
    }

    /* ── Dropzone ─────────────────────────────────────────────── */
    function initDropzone() {
        var $zone  = $('#dropzone');
        var $input = $('#fileInput');
        var $prev  = $('#filePreview');

        if (!$zone.length) return;

        // Drag events
        $zone.on('dragover dragenter', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $zone.addClass('drag-over');
        });
        $zone.on('dragleave drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $zone.removeClass('drag-over');
        });
        $zone.on('drop', function (e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $input[0].files = files;
                showFilePreview(files[0]);
            }
        });

        // Click on dropzone triggers the hidden input
        $zone.on('click', function (e) {
            if (!$(e.target).is('input')) $input.trigger('click');
        });

        $input.on('change', function () {
            if (this.files.length) showFilePreview(this.files[0]);
        });

        function showFilePreview(file) {
            var maxBytes = parseInt($('#uploadForm').data('max-bytes') || 20971520, 10);
            if (file.size > maxBytes) {
                alert('Tiedosto on liian suuri (' + formatBytes(file.size) + '). Maksimi on ' + formatBytes(maxBytes) + '.');
                $input.val('');
                $prev.addClass('hidden').empty();
                return;
            }
            var allowedExt = ($('#uploadForm').data('allowed-ext') || 'pdf,png,jpg,jpeg').split(',');
            var ext = file.name.split('.').pop().toLowerCase();
            if (!allowedExt.includes(ext)) {
                alert('Tiedostomuoto .' + ext + ' ei ole sallittu. Sallitut: ' + allowedExt.join(', '));
                $input.val('');
                $prev.addClass('hidden').empty();
                return;
            }
            var icon = ext === 'pdf' ? '📄' : '🖼️';
            $prev.removeClass('hidden').html(
                '<span class="file-icon">' + icon + '</span>' +
                '<span class="file-name">' + escHtml(file.name) + '</span>' +
                '<span class="file-size">' + formatBytes(file.size) + '</span>' +
                '<button type="button" class="btn-remove" title="Poista">✕</button>'
            );
            $prev.find('.btn-remove').on('click', function () {
                $input.val('');
                $prev.addClass('hidden').empty();
            });
        }
    }

    /* ── Dynamic signer rows ──────────────────────────────────── */
    function initSignerRows() {
        var $list = $('#signersList');
        if (!$list.length) return;

        reIndexRows();

        // Remove row
        $list.on('click', '.btn-remove-signer', function () {
            var $rows = $list.find('.signer-row');
            if ($rows.length <= 1) {
                showToast('Vähintään yksi allekirjoittaja vaaditaan.', 'warning');
                return;
            }
            $(this).closest('.signer-row').slideUp(180, function () {
                $(this).remove();
                reIndexRows();
            });
        });

        // Add row
        $('#addSigner').on('click', function () {
            var $rows = $list.find('.signer-row');
            if ($rows.length >= 20) {
                showToast('Enintään 20 allekirjoittajaa.', 'warning');
                return;
            }
            var idx  = $rows.length;
            var $row = $(
                '<div class="signer-row" data-index="' + idx + '">' +
                '  <div class="signer-num">' + (idx + 1) + '</div>' +
                '  <div class="signer-fields">' +
                '    <input type="text" name="signers[' + idx + '][name]" class="form-control" placeholder="Nimi *" required maxlength="150">' +
                '    <input type="email" name="signers[' + idx + '][email]" class="form-control" placeholder="Sähköposti *" required maxlength="200">' +
                '  </div>' +
                '  <button type="button" class="btn btn-danger btn-sm btn-remove-signer" title="Poista">✕</button>' +
                '</div>'
            );
            $row.hide().appendTo($list).slideDown(200);
            $row.find('input').first().focus();
        });

        function reIndexRows() {
            $list.find('.signer-row').each(function (i) {
                $(this).attr('data-index', i);
                $(this).find('.signer-num').text(i + 1);
                $(this).find('input[name*="[name]"]').attr('name',  'signers[' + i + '][name]');
                $(this).find('input[name*="[email]"]').attr('name', 'signers[' + i + '][email]');
            });
        }
    }

    /* ── Flash auto-dismiss ─────────────────────────────────────── */
    function initFlashAutoDismiss() {
        var $flash = $('#flashMessage');
        if (!$flash.length) return;
        setTimeout(function () {
            $flash.fadeOut(400, function () { $(this).remove(); });
        }, 6000);
    }

    /* ── Confirm before form submit ─────────────────────────────── */
    function initConfirmForms() {
        $('[data-confirm]').on('submit', function (e) {
            var msg = $(this).data('confirm');
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    }

    /* ── Upload form — spinner on submit ─────────────────────────── */
    function initUploadForm() {
        var $form = $('#uploadForm');
        if (!$form.length) return;
        $form.on('submit', function () {
            var $btn = $('#uploadSubmit');
            $btn.prop('disabled', true)
                .find('.btn-text').addClass('hidden');
            $btn.find('.btn-spinner').removeClass('hidden');
        });
    }

    /* ── Toast (lightweight notification) ─────────────────────── */
    function showToast(message, type) {
        type = type || 'info';
        var $toast = $('<div class="flash flash-' + type + '" style="position:fixed;top:1rem;right:1rem;z-index:9999;max-width:340px">' +
            '<span>' + escHtml(message) + '</span></div>');
        $('body').append($toast);
        setTimeout(function () { $toast.fadeOut(300, function () { $(this).remove(); }); }, 3500);
    }

    /* ── Helpers ─────────────────────────────────────────────── */
    function formatBytes(bytes) {
        if (bytes < 1024)       return bytes + ' B';
        if (bytes < 1048576)    return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function escHtml(str) {
        return $('<span>').text(String(str)).html();
    }

})(jQuery);
