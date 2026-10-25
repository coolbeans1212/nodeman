<?php
$sql = "SELECT id,key_required,access_key,date_created,filename,filesize,mime FROM utility_file_uploads ORDER BY date_created DESC";
$stmt = $mysqli->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>
<?php
if (mysqli_num_rows($result) > 0) {
    ?>
    <table id="utility-file-upload-table">
    <thead>
        <tr>
            <th>File</th>
            <th>Key</th>
            <th>Date Uploaded</th>
            <th>File Size</th>
            <th>File Type</th>
            <th>Access URL</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    while ($file = mysqli_fetch_assoc($result)) {
        ?>
        <tr id="<?php echo 'utility-file-upload-table-row-' . $file['id']; ?>">
            <td data-field="filename"><?php echo $file['filename']; ?></td>
            <td data-field="key"><?php echo $file['access_key']; ?></td>
            <td data-field="date-uploaded"><?php echo $file['date_created']; ?></td>
            <td data-field="filesize"><?php echo $file['filesize']; ?></td>
            <td data-field="filetype"><?php echo $file['mime']; ?></td>
            <?php
            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
            }
            else {
            $protocol = 'http://';
            }
            $accessUrl = $protocol . $_SERVER['SERVER_NAME'] . '/utilities/retrieveFile.php?i=' . $file['id'];
            if ($file['key_required']) {
                $accessUrl .= '&k=' . urlencode($file['access_key']);
            }
            ?>
            <td data-field="access-url"><button class="thin-button" onclick="navigator.clipboard.writeText('<?php echo $accessUrl;?>')">Click to copy</button></td>
            <td data-field="actions"><button class="thin-button utility-file-upload-edit-button" data-id="<?php echo $file['id']?>" data-key-required="<?php echo $file['key_required'] ? '1' : '0'; ?>" data-key="<?php echo $file['access_key'];?>">Edit</button></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<?php
}
