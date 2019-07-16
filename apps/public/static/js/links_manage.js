jQuery(document).ready(function () {
    let edit_form = $('#link_edit_form');
    let remove_form = $('#link_remove_form');

    $('.link-edit').click(function () {
        let that = $(this);

        $('#links_modal').modal();

        // Get link data from <tr> and Fill link data to form
        let tr = $('#links_manager_table tr[data-id=' + that.data('id') + ']');
        for (let datum in tr.data()) {
            edit_form.find('[name="link_' + datum + '"]').val(tr.data(datum));
        }
    });

    $('.link-remove').click(function () {
        let that = $(this);
        if (confirm('Confirm to remove this links ?')) {
            remove_form.find('input[name=link_id]').val(that.data('id'));
            remove_form.submit();
        }
    });

});
