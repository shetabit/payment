<?php

namespace Shetabit\Payment\Drivers;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\{InvalidPaymentException, PurchaseFailedException};
use Shetabit\Payment\{Invoice, Receipt};

class Saderat extends Driver
{
    /**
     * Invoice
     *
     * @var Invoice
     */
    protected $invoice;

    /**
     * Driver settings
     *
     * @var object
     */
    protected $settings;

    /**
     * Saderat constructor.
     * Construct the class with the relevant settings.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object)$settings;
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     */
    public function purchase()
    {
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        $data = array(
            'Amount' => $this->invoice->getAmount() * 10, // convert to rial
            'TerminalID' => $this->settings->terminalId,
            'callbackURL' => $this->settings->callbackUrl,
            'InvoiceID' => crc32($this->invoice->getUuid()),
            'uuid' => $this->invoice->getUuid(), // we add it to make transactionId to be unique
        );

        // convert data to base64 so we can transfer it as transactionId
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        $transactionId = base64_encode($json);

        $this->invoice->transactionId($transactionId);

        // return the transaction's id
        return $this->invoice->getTransactionId();
    }

    /**
     * Pay the Invoice
     *
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function pay()
    {
        $payUrl = $this->settings->apiPaymentUrl;

        $json = base64_decode($this->invoice->getTransactionId());
        $data = json_decode($json, true);

        if (!empty($data['uuid'])) { // we dont need uuid any more
           unset($data['uuid']);
        }

        return $this->redirectWithForm($payUrl, $data, 'POST');
    }

    /**
     * Verify payment
     *
     * @return mixed|void
     * @throws InvalidPaymentException
     */
    public function verify()
    {
        $digitalReceipt = request()->get('digitalreceipt');
        $terminalId = $this->settings->terminalId ?? request()->get('terminalid');
        $rrn = request()->get('rrn');

        /**
         * رسید دیجیتال
         * $digitalreceipt
         * شماره ترمینال
         * $terminalid
         * شماره ارجاع
         * $rrn
         **/

        if ( $digitalReceipt && $terminalId != "") {
            $digitalreceipt = test_input($_POST["digitalreceipt"]);
            $terminalid = test_input($_POST["terminalid"]);

            $data = array(
                "digitalreceipt"=>$digitalreceipt,
                "Tid"=>$terminalid,
            );

            $dataQuery = http_build_query($data);

            if (request()->get('inject')) {
                $url="https://mabna.shaparak.ir:8081/V1/PeymentApi/RollBack";
                $msg="برگشت تراکنش :";
            } else {
                $url="https://mabna.shaparak.ir:8081/V1/PeymentApi/Advice";
                $msg="تایید تراکنش :";
            }
        }

$Ipg_Array = IpgRequest('POST',$dataQuery,$url);
$decode_Ipg_Array=json_decode($Ipg_Array);

$status=$decode_Ipg_Array->Status;
$ReturnId=$decode_Ipg_Array->ReturnId;
$message=$decode_Ipg_Array->Message;

echo "<br><br>".$msg."<br>";
echo "<br>status : <br>";
echo $status."<br>";
echo "<br>ReturnId : <br>";
echo $ReturnId."<br>";
echo "<br>message : <br>";
echo $message."<br><br>";


				<form method="POST" action="">
						<div>
							<input type="hidden" name="digitalreceipt" value="<?php echo $digitalreceipt; ?>" />
						</div>
						<div>
							<input type="hidden" name="terminalid" value="<?php echo $terminalid; ?>" />
						</div>
							<label>&nbsp;</label>
							<input type="submit" name="inject" value="برگشت تراکنش" class="submit" />
						</div>

				</form>

        return $this->createReceipt($data['referenceNumber']);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    public function createReceipt($referenceId)
    {
        $receipt = new Receipt('saderat', $referenceId);

        return $receipt;
    }
}
