<?php
namespace modmore\Commerce_mPDFWriter\Modules;
use modmore\Commerce\Events\PDFWriter;
use modmore\Commerce\Modules\BaseModule;
use modmore\Commerce_mPDFWriter\Writer;
use Symfony\Component\EventDispatcher\EventDispatcher;


require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

class mPDFWriter extends BaseModule {

    public function getName()
    {
        $this->adapter->loadLexicon('commerce_mpdfwriter:default');
        return $this->adapter->lexicon('commerce_mpdfwriter');
    }

    public function getAuthor()
    {
        return 'Murray Wood';
    }

    public function getDescription()
    {
        return $this->adapter->lexicon('commerce_mpdfwriter.module_description');
    }

    public function initialize(EventDispatcher $dispatcher)
    {
        // Load our lexicon
        $this->adapter->loadLexicon('commerce_mpdfwriter:default');

        // Load PDF Writer
        $dispatcher->addListener(\Commerce::EVENT_GET_PDF_WRITER, array($this, 'getPDFWriter'));
    }

    public function getPDFWriter(PDFWriter $event)
    {
        $instance = new Writer();
        $event->addWriter($instance);
    }

    public function getModuleConfiguration(\comModule $module)
    {
        return [];
    }
}
