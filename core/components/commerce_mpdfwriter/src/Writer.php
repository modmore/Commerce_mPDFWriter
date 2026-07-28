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
    /** @var resource|null */
    private $target;
    /** @var string|null */
    private $targetPath;
    /** @var string|null */
    private $source;

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
        // Close any previous handle before opening a new target.
        $this->closeTarget(false);

        $this->targetPath = $file;
        $this->target = fopen($file, 'wb+');
        if (!$this->target) {
            $this->targetPath = null;
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
        // Make sure we have a valid objective
        if ($this->source === null) {
            $this->closeTarget(true);
            throw new MissingSourceException('Source HTML string not provided');
        }
        if (!$this->target) {
            throw new InvalidOutputException('Could not open target stream.');
        }

        // mPDF is not safe to reuse across documents; always create a fresh instance.
        // Reusing one instance can write the previous document's content under a new filename.
        $mpdf = new Mpdf();

        // Set the base path to the root of the site for relative image/asset URLs.
        // While the docs at  https://mpdf.github.io/reference/mpdf-functions/setbasepath.html don't mention the ability
        // to provide a server path, like we're doing here, it seems to work most reliably.
        // Internally mpdf fetches with fopen/file_get_contents, so this works a treat
        $mpdf->SetBasePath(MODX_BASE_PATH);

        try {
            $mpdf->WriteHTML($this->source);
            $binary = $mpdf->OutputBinaryData();
            fwrite($this->target, $binary);
            $this->closeTarget(false);
            $this->source = null;
            return $binary;
        } catch (MpdfException $e) {
            $this->closeTarget(true);
            $this->source = null;
            throw new RenderException('Failed generating PDF: ' . $e->getMessage(), $e->getCode(), $e);
        } catch (\Throwable $e) {
            $this->closeTarget(true);
            $this->source = null;
            throw $e;
        }
    }

    /**
     * Close the output stream and optionally remove a truncated/incomplete file.
     *
     * @param bool $deleteFile
     * @return void
     */
    private function closeTarget($deleteFile)
    {
        if (is_resource($this->target)) {
            fclose($this->target);
        }
        $this->target = null;

        if ($deleteFile && $this->targetPath && file_exists($this->targetPath)) {
            @unlink($this->targetPath);
        }
        $this->targetPath = null;
    }
}
