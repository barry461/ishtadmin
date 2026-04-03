<?php

namespace website\Views\js;

use http\Exception\RuntimeException;
use website\Template;
use website\Views\html\DomBuilder;
use website\Views\html\DomNode;
use website\Views\html\HtmlTokenizer;

class VueTemplateCompiler
{
    /**
     * @var Template
     */
    protected $Template = null;
    protected $compileDir = null;
    protected $tplFile = null;
    protected $tplDir = null;


    public function __construct($tplDir, $tplFile, $compileDir , $Template)
    {
        $this->tplDir = $tplDir;
        $this->tplFile = $tplFile;
        $this->compileDir = $compileDir;
        $this->Template = $Template;
    }


    public function compileIfChange(): string
    {
        $tplFile = $this->tplDir . $this->tplFile;
        $compileFile = $this->compileDir . $this->tplFile . '.php';
        $time1 = filemtime($tplFile);
        $time2 = file_exists($compileFile)  ? filemtime($compileFile) : 0;
        if ($time1 > $time2 || true){
            $source = file_get_contents($tplFile);
            $compileSource = $this->compile($source);
            $compileDir = dirname($compileFile) ;
            if (!file_exists($compileDir)){
                @mkdir($compileDir , 0755 , true);
            }
            $compileSource = "<?php 
/**
 * @var \website\Template \$this 
 */
?>\n" . trim($compileSource);
            file_put_contents($compileFile , $compileSource);
        }
        return $compileFile;
    }


    /**
     * @throws \Exception
     */
    protected function compile(string $template): string
    {
        $tokens = (new HtmlTokenizer($template))->tokenize();
        $dom = DomBuilder::build($tokens);
        $scripts = $dom->querySelectorAll('script');
        $imports = $this->importTables($scripts);
        $components = $this->componentTables($scripts);
        $components = array_keys($imports);
        $importTables = [];
        foreach ($components as $component) {
            if (!isset($imports[$component])) {
                throw new RuntimeException('组件导入表不存在, '.$component);
            }
            $importTables[$component] = $imports[$component];
        }
        foreach ($importTables as $component => $file) {
            $compileFile = $this->compileDir . $file;
            $file = $this->tplDir . $file;
            $compileDir = dirname($compileFile) . '/';
            $compile = new self(dirname($file) . '/', basename($file), $compileDir , $this->Template);
            $this->Template->registerComponent($component, $compile->compileIfChange());
        }
        VueDirectiveCompiler::$components = $importTables;
        return VueDirectiveCompiler::compileChildren($dom->children);
    }



    protected function importTables($scripts): array
    {
        $imports = [];
        /** @var DomNode $script */
        foreach ($scripts as $script) {
            $code = $script->innerHTML();
            if (preg_match_all("/import\s+([^\s]+)\s+from\s+([\"']+)(.*)\\2;*/i", $code, $matches)) {
                foreach ($matches[1] as $k => $import) {
                    $code = str_replace($matches[0][$k] , '' , $code);
                    $from = $matches[3][$k];
                    $imports[$import] = trim($from, "'\";");
                }
                $script->setInnerHTML($code);
            }
        }
        return $imports;
    }

    protected function componentTables($scripts): array
    {
        $components = [];
        foreach ($scripts as $script) {
            $code = $script->innerHTML();
            if (preg_match("/components:\s*\{\s*([^}]+?)\s*}\s*/", $code, $matches)) {
                $matches = explode(',', $matches[1]);
                foreach ($matches as $match) {
                    $match = trim($match);
                    list($component) = explode(':', $match, 2);
                    $components[] = $component;
                }
            }
        }

        return $components;
    }

}