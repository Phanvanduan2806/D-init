/*
* EXPORT
* */
jQuery(function ($) {
    // BUTTON SCAN EXPORT
    $(`<div>
<button class="button button-large hidden" id="uih-scan-export-option">Scan option</button>
<button class="button button-large hidden" id="uih-scan-export-section">Scan sections</button>
<button class="button button-large hidden" id="uih-scan-export-re">Re-scan</button>
<button class="button button-primary button-large hidden" id="uih-scan-export-submit">Submit</button>
</div>
`)
        .insertAfter($('.post-type-exports .page-title-action'));
    if ($('.post-type-exports .wp-list-table').length) {
        $('#uih-scan-export-option, #uih-scan-export-section').removeClass('hidden');
    }
    if ($('.post-type-exports #publish').length) {
        $('#uih-scan-export-re, #uih-scan-export-submit').removeClass('hidden');
    }
    // EXPORT-SUBMIT
    $("body").on("click", "#uih-scan-export-submit", async function () {
        const post_id = $('[name="post_ID"]').val();
        if (!post_id) {
            alert(`Không tìm thấy: POST_ID`);
            return;
        }
        const res = await httpApi.post('/scan-export-submit', {post_id});
        console.log('res', res);
    });
    // EXPORT-RE
    $("body").on("click", "#uih-scan-export-re", async function () {
        const label = $('[name="post_title"]').val();
        if (!label) {
            alert(`Không tìm thấy: LABEL`);
            return;
        }
        const res = await httpApi.post('/scan-export-re', {label});
        console.log('res', res);
    });
    // EXPORT-SECTION
    $("body").on("click", "#uih-scan-export-section", async function () {
        const res = await httpApi.post('/scan-export-section');
        console.log('res', res);
    });
    // EXPORT-OPTION
    $("body").on("click", "#uih-scan-export-option", async function () {
        const res = await httpApi.post('/scan-export-option');
        console.log('res', res);
    });

    $('.post-type-exports [name="post_title"]').attr('readonly', 'readonly');
    $(`[data-name="export_data"] textarea`).attr('readonly', 'readonly');
    $(`[data-name="menu"] textarea`).attr('readonly', 'readonly');
    $(`[data-name="blocks"] textarea`).attr('readonly', 'readonly');
});
/*
* SOURCE
* */
jQuery(function ($) {
    $(`<div>
<button class="button button-large " id="uih-show-source">Import</button>
</div>
`)
        .insertAfter($('.post-type-sources .page-title-action'));

    $("body").on("click", "#uih-show-source", async function () {
        const iframeContent = `<iframe src="${php_obj.home}/code-list?key=${php_obj.key}" width="100%" height="100%"></iframe>`;
        tb_show('Import', '#TB_inline?width=900&height=400&inlineId=my-custom-content');
        $('#TB_ajaxContent').empty().append(iframeContent);
    });
});
//  MESSAGE SOURCE
window.addEventListener('message', async (event) => {
    const code_id = event.data.code_id;
    if (!code_id) return;
    if (!confirm(`Bạn muốn import source`)) return;
    const res = await httpApi.post('/code-master', {code_id});
    console.log('res', res);
});
// MERGE SOURCE BUTTON
jQuery(document).ready(function ($) {
    $('.post-type-sources .row-title').each(function () {
        const title = $(this).text();
        var parentRow = $(this).closest('tr');
        var post_id = parentRow.find('input[type="checkbox"]').val();

        var writeButton = $('<button/>', {
            type: 'button',
            text: 'Merge',
            class: 'button button-primary',
            style: 'margin-left: .3rem',
            click: async function () {
                if (!confirm(`Bạn muốn merge source`)) return;
                $(this).text('Merging...');
                const res = await httpApi.post('/merge-source', {post_id});
                console.log('res', res);
                let html='';
                if (res.functions_php) {
                    html += `<div>
            <button class="wpfh-copy-button">copy functions</button>
            <textarea style="width: 100%" rows="2">${res.functions_php}</textarea>
        </div>`;
                }
                if (res.how_to_use) {
                    html += `<div>
            <button class="wpfh-copy-button">copy how_to_use</button>
            <textarea style="width: 100%" rows="2">${res.how_to_use}</textarea>
        </div>`;
                }
                $('#TB_ajaxContent').html(`<h1>DONE</h1><h2>Request: /merge-source</h2>${html}`);
                $(this).text('Done');
            }
        });
        $(writeButton).insertAfter($(this));
    });
});
/*
* IMPORT
* */
jQuery(function ($) {
    $(`<div>
<button class="button button-large " id="uih-show-import">Import</button>
<button class="button button-primary button-large " id="uih-merge-import">Merge</button>
</div>
`)
        .insertAfter($('.post-type-imports .page-title-action'));
//  SHOW-IMPORT
    $("body").on("click", "#uih-show-import", async function () {
        const iframeContent = `<iframe src="${php_obj.home}/store-list?key=${php_obj.key}" width="100%" height="100%"></iframe>`;
        tb_show('Import', '#TB_inline?width=900&height=700&inlineId=my-custom-content');
        $('#TB_ajaxContent').empty().append(iframeContent);
    });
//  MERGE-IMPORT
    $("body").on("click", "#uih-merge-import", async function () {
        const iframeContent = `<iframe src="/merge-import" width="100%" height="100%"></iframe>`;
        tb_show('Merge', '#TB_inline?width=800&height=400&inlineId=my-custom-content');
        $('#TB_ajaxContent').empty().append(iframeContent);
        // const res = await httpApi.post('/merge-import');
        // console.log('res', res);
    });
//  MESSAGE IMPORT
    window.addEventListener('message', async (event) => {
        const store_id = event.data.store_id;
        if (!store_id) return;
        if (!confirm(`Bạn muốn import section`)) return;
        const res = await httpApi.post('/import-master', {store_id});
        console.log('res', res);
    });

// COPY BUTTON
    $("body").on("click", ".wpfh-copy-button", function () {
        var textarea = $(this).parent().find('textarea');
        textarea.select();
        document.execCommand("copy");
        $(this).text('copied');
    });
//  MESSAGE MERGE
    window.addEventListener('message', async (event) => {
        const {import_ids, page_id} = event.data;
        if (!import_ids || !page_id) return;
        if (!confirm(`Bạn muốn merge section`)) return;
        const res = await httpApi.post('/import-merge', {import_ids, page_id});
        console.log('res', res);
        const html = `<div>
            <button class="wpfh-copy-button">copy</button>
            <textarea style="width: 100%" rows="10">${res.enqueue_code}</textarea>
        </div>`;
        $('#TB_ajaxContent').html(`<h1>DONE</h1><h2>Request: /import-merge</h2>${html}`);
    });
});
// STICKY SAVE BUTTON
jQuery(document).ready(function ($) {
    if (!($('#poststuff').length && $('#postbox-container-1').length)) return;

    function updateHeights() {
        var poststuffHeight = $('#poststuff').height();
        $('#postbox-container-1').height(poststuffHeight);
    }

    updateHeights();
    $(window).resize(function () {
        updateHeights();
    });
});