<?php
header('Content-Type: application/json');
echo file_get_contents('../../data/invoice_settings.json');
