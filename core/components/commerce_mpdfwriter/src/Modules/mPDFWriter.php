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

        // Add the xPDO package, so Commerce can detect the derivative classes
//        $root = dirname(dirname(__DIR__));
//        $path = $root . '/model/';
//        $this->adapter->loadPackage('commerce_mpdfwriter', $path);

        // Add template path to twig
//        /** @var ChainLoader $loader */
//        $root = dirname(dirname(__DIR__));
//        $loader = $this->commerce->twig->getLoader();
//        $loader->addLoader(new FilesystemLoader($root . '/templates/'));
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
