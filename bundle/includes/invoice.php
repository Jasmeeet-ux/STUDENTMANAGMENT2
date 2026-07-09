<?php
function generateInvoiceHTML($userName, $userEmail, $purchaseDetails, $invoiceNumber, $purchaseDate) {
    // $purchaseDetails is an array of purchased items with keys: title, price, quantity, total
    $totalAmount = 0;
    foreach ($purchaseDetails as $item) {
        $totalAmount += $item['total'];
    }

    $logoCid = 'logo_cid';

    $html = '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .invoice-box {
                max-width: 800px;
                margin: auto;
                padding: 30px;
                border: 1px solid #eee;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
                font-size: 16px;
                line-height: 24px;
                color: #555;
            }
            .invoice-box table {
                width: 100%;
                line-height: inherit;
                text-align: left;
                border-collapse: collapse;
            }
            .invoice-box table td {
                padding: 5px;
                vertical-align: top;
            }
            .invoice-box table tr.heading td {
                background: #2563eb;
                color: white;
                font-weight: bold;
            }
            .invoice-box table tr.item td {
                border-bottom: 1px solid #eee;
            }
            .invoice-box table tr.total td:nth-child(4) {
                border-top: 2px solid #2563eb;
                font-weight: bold;
            }
            .logo {
                max-width: 150px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="invoice-box">
            <img src="cid:' . $logoCid . '" alt="Company Logo" class="logo" />
            <h2>Payment Invoice</h2>
            <p><strong>Invoice Number:</strong> ' . htmlspecialchars($invoiceNumber) . '</p>
            <p><strong>Purchase Date:</strong> ' . htmlspecialchars($purchaseDate) . '</p>
            <p><strong>Billed To:</strong> ' . htmlspecialchars($userName) . ' (' . htmlspecialchars($userEmail) . ')</p>
            <table>
                <tr class="heading">
                    <td>Item</td>
                    <td>Price</td>
                    <td>Quantity</td>
                    <td>Total</td>
                </tr>';

    foreach ($purchaseDetails as $item) {
        $html .= '
                <tr class="item">
                    <td>' . htmlspecialchars($item['title']) . '</td>
                    <td>₹' . number_format($item['price'], 2) . '</td>
                    <td>' . htmlspecialchars($item['quantity']) . '</td>
                    <td>₹' . number_format($item['total'], 2) . '</td>
                </tr>';
    }

    $html .= '
                <tr class="total">
                    <td colspan="3"></td>
                    <td>₹' . number_format($totalAmount, 2) . '</td>
                </tr>
            </table>
            <p>Thank you for your purchase!</p>
        </div>
    </body>
    </html>';

    return $html;
}
?>
