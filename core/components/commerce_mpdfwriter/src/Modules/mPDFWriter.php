<?php
namespace modmore\Commerce_mPDFWriter\Modules;
use modmore\Commerce\Admin\Configuration\About\ComposerPackages;
use modmore\Commerce\Admin\Sections\SimpleSection;
use modmore\Commerce\Events\Admin\PageEvent;
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
        return 'modmore';
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
        $dispatcher->addListener(\Commerce::EVENT_GET_PDF_WRITER, [$this, 'getPDFWriter']);
        $dispatcher->addListener(\Commerce::EVENT_DASHBOARD_LOAD_ABOUT, [$this, 'addLibrariesToAbout']);
    }

    public function getPDFWriter(PDFWriter $event)
    {
        $instance = new Writer();
        $event->addWriter($instance);
    }

    public function addLibrariesToAbout(PageEvent $event)
    {
        $lockFile = dirname(dirname(__DIR__)) . '/composer.lock';
        if (file_exists($lockFile)) {
            $section = new SimpleSection($this->commerce);
            $section->addWidget(new ComposerPackages($this->commerce, [
                'lockFile' => $lockFile,
                'heading' => $this->adapter->lexicon('commerce.about.open_source_libraries') . ' - ' . $this->adapter->lexicon('commerce_mpdfwriter'),
                'introduction' => '', // Could add information about how libraries are used, if you'd like
            ]));

            $about = $event->getPage();
            $about->addSection($section);
        }
    }

    public function getModuleConfiguration(\comModule $module)
    {
        return [];
    }
}
