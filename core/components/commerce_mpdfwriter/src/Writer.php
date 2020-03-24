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
        // Set the base path to the root of the site for relative image/asset URLs.
        // While the docs at  https://mpdf.github.io/reference/mpdf-functions/setbasepath.html don't mention the ability
        // to provide a server path, like we're doing here, it seems to work most reliably.
        // Internally mpdf fetches with fopen/file_get_contents, so this works a treat
        $this->mpdf->SetBasePath(MODX_BASE_PATH);

        // Make sure we have a valid objective
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