<?php

namespace modmore\Commerce\Tests\Modules;

use modmore\Commerce\Events\PDFWriter;
use modmore\Commerce\PDF\Writer\WriterInterface;
use modmore\Commerce_mPDFWriter\Modules\mPDFWriter;
use modmore\Commerce_mPDFWriter\Writer;
use modmore\Commerce\Dispatcher\EventDispatcher;

class mPDFWriterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Commerce $commerce */
    public $commerce;
    /** @var \modmore\Commerce\Adapter\AdapterInterface $adapter */
    public $adapter;

    public function setUp()
    {
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
}
