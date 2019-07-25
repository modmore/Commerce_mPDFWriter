<?php
namespace modmore\Commerce_mPDFWriter;
use modmore\Commerce\PDF\Exception\InvalidOutputException;
use modmore\Commerce\PDF\Exception\MissingSourceException;
use modmore\Commerce\PDF\Exception\RenderException;
use modmore\Commerce\PDF\Writer\FromHtmlWriterInterface;
use modmore\Commerce\PDF\Writer\WriterInterface;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

final class Writer implements WriterInterface, FromHtmlWriterInterface
{
    /** @var resource */
    private $target;
    /** @var string */
    private $source;
    /** @var Mpdf */
    private $mpdf;

    public function __construct()
    {
        $this->mpdf = new Mpdf();
    }

    /**
     * @param string $html
     * @return void
     */
    public function setSourceHtml($html)
    {
        $this->source = $html;
    }
    /**
     * @param string $file
     * @return void
     * @throws InvalidOutputException
     */
    public function setOutputFile($file)
    {
        $this->target = fopen($file, 'wb+');
        if (!$this->target) {
            throw new InvalidOutputException('Could not open target stream.');
        }
    }

    /**
     * @param array $options
     * @return string
     * @throws InvalidOutputException
     * @throws MissingSourceException
     * @throws RenderException
     */
    public function render(array $options = [])
    {

        if ($this->source === null) {
            throw new MissingSourceException('Source HTML string not provided');
        }
        if (!$this->target) {
            throw new InvalidOutputException('Could not open target stream.');
        }
        try {
            $this->mpdf->WriteHTML($this->source);
            $binary = $this->mpdf->Output('','S');
        } catch (MpdfException $e) {
            throw new RenderException('Failed generating PDF: ' . $e->getMessage(), $e->getCode(), $e);
        }
        fwrite($this->target, $binary);
        fclose($this->target);
        return $binary;
    }
}