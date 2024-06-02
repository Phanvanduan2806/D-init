class httpApi {
    static async post(url, data) {
        return new Promise((resolve, reject) => {
            jQuery.ajax({
                url: `/wp-json/ui-helper/v1${url}`,
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                beforeSend: function (xhr) {
                    // jQuery('.c-modal').click();
                    tb_show('', '#TB_inline?width=300&height=300&inlineId=my-custom-content'); // Change dimensions as needed
                    jQuery('#TB_ajaxContent').html(`<h1>LOADING..</h1><h2>Request: ${url}</h2>`);
                },
                success: function (response) {
                    const res = JSON.stringify(response, null, 2);
                    jQuery('#TB_ajaxContent').html(`<h1>DONE</h1><h2>Request: ${url}</h2><pre>${res}</pre>`);
                    resolve(response);
                },
                error: function (xhr, status, error) {
                    const res = JSON.stringify(xhr.responseJSON, null, 2);
                    jQuery('#TB_ajaxContent').html(`<h1>ERROR</h1><h2>Request: ${url}</h2><pre>${res}</pre>`);
                    reject(error);
                }
            });
        });
    }
}
