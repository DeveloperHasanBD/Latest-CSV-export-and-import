<div class="import_csv_form">
    <?php
    glossario_csv_import_processing();
    ?>
    <form enctype='multipart/form-data' action='' method='post'>
        <input class="mt-3 form-control" type='file' name='glossario_csv_file'>
        <input class="form-control mt-2 btn btn-info" type="submit" value="Upload glossario CSV" name="glossario_submit_btn">
    </form>
</div>