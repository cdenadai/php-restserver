<?php

namespace ByJG\RestServer;

class ServiceHandler
{
    protected $output = Output::JSON;

    public function getOutput()
    {
        return $this->output;
    }

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function __construct($output)
    {
        $this->setOutput($output);
    }

    public function execute($class)
    {
        if (!class_exists($class))
        {
            throw new Exception\ClassNotFoundException("Class $class not found");
        }

        $instance = new $class();

        if (!($instance instanceof ServiceAbstract))
        {
            throw new Exception\InvalidClassException("Class $class is not instance of ServiceAbstract");
        }

		$method = strtolower($instance->getRequest()->server("REQUEST_METHOD"));

		$customAction = $method . ($instance->getRequest()->get('action'));

		if (method_exists($instance, $customAction)) {
            $instance->$customAction();
        } else {
            throw new BadMethodCallException("The method '$customAction' does not exists.");
        }

        $dom = $instance->getResponse()->getResponseBag()->process();

        switch ($this->getOutput())
        {
            case Output::JSON:
                echo \ByJG\AnyDataset\Model\ObjectHandler::xml2json($dom);
                break;

            case Output::XML:
                echo $dom->saveXML();
                break;

            case Output::CSV:
                $array = \ByJG\Util\XmlUtil::xml2Array($dom);
                foreach ($array as $line)
                {
                    foreach($line as $field)
                    {
                        echo "\"$field\";"
                    }
                    echo "\n";
                }
                break;

        }
    }
}