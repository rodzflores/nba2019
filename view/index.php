<html>
    <head>
    <style type="text/css">
        body {
            font: 16px Roboto, Arial, Helvetica, Sans-serif;
        }
        td, th {
            padding: 4px 8px;
        }
        th {
            background: #eee;
            font-weight: 500;
        }
        tr:nth-child(odd) {
            background: #f4f4f4;
        }
    </style>
    </head>
    <body>
        <?php
            if (isset($_SESSION['flash'])) {
                echo $_SESSION['flash'];
                unset($_SESSION['flash']);
            }
        ?>
        <table>
            <thead>
                <tr>
                    <?php foreach ($this->data['headings'] as $heading) { ?>
                        <th><?= $heading ?></th>
                    <?php } ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($this->data['rows'] as $row) { ?>
                        <tr>
                            <?php foreach ($row as $value) {?>
                                <td><?= $value ?></td>
                            <?php } ?>
                        </tr>
                <?php } ?>
            </tbody>
        </table>
        
    </body>
</html>