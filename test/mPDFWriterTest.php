<?php

namespace modmore\Commerce\Tests\Modules;

use modmore\Commerce\Events\PDFWriter;
use modmore\Commerce\PDF\Writer\WriterInterface;
use modmore\Commerce_mPDFWriter\Modules\mPDFWriter;
use modmore\Commerce_mPDFWriter\Writer;
use modmore\Commerce\Dispatcher\EventDispatcher;

class mPDFWriterTest extends \Yoast\PHPUnitPolyfills\TestCases\TestCase
{
    /** @var \Commerce $commerce */
    public $commerce;
    /** @var \modmore\Commerce\Adapter\AdapterInterface $adapter */
    public $adapter;

    protected function setUp(): void
    {
        parent::setUp();
        global $commerce;
        $this->commerce = $commerce;
        $this->adapter = $this->commerce->adapter;
    }

    public function testModuleRegistering()
    {
        $dispatcher = new EventDispatcher();
        $module = new mPDFWriter($this->commerce);

        $module->initialize($dispatcher);
        self::assertCount(2, $dispatcher->getListeners());

        $event = new PDFWriter();

        $module->getPDFWriter($event);
        $writers = $event->getWriters();
        self::assertCount(1, $writers);
        $writer = reset($writers);
        self::assertInstanceOf(WriterInterface::class, $writer);
        self::assertInstanceOf(Writer::class, $writer);
    }

    /**
     * The writer is cached for the request by Commerce::getPDFWriter(); each
     * render must produce independent PDF content for its own HTML source.
     */
    public function testRenderProducesDistinctPdfsWhenWriterIsReused()
    {
        $dir = sys_get_temp_dir() . '/commerce_mpdfwriter_test_' . uniqid('', true);
        mkdir($dir);

        $fileA = $dir . '/invoice-a.pdf';
        $fileB = $dir . '/invoice-b.pdf';

        try {
            $writer = new Writer();

            $writer->setSourceHtml('<html><body><h1>Invoice A UNIQUE-TOKEN-AAA</h1><p>' . str_repeat('A', 200) . '</p></body></html>');
            $writer->setOutputFile($fileA);
            $pdfA = $writer->render();

            $writer->setSourceHtml('<html><body><h1>Invoice B UNIQUE-TOKEN-BBB</h1><p>' . str_repeat('B', 200) . '</p></body></html>');
            $writer->setOutputFile($fileB);
            $pdfB = $writer->render();

            // Sticky mPDF reuse writes the first document under both filenames.
            self::assertNotSame($pdfA, $pdfB);
            self::assertGreaterThan(100, strlen($pdfA));
            self::assertGreaterThan(100, strlen($pdfB));
            self::assertSame($pdfA, file_get_contents($fileA));
            self::assertSame($pdfB, file_get_contents($fileB));
        } finally {
            foreach ([$fileA, $fileB] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}
