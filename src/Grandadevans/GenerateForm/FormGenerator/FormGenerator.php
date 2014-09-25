<?php namespace Grandadevans\GenerateForm\FormGenerator;

use Grandadevans\GenerateForm\BuilderClasses\OutputBuilder;
use Grandadevans\GenerateForm\BuilderClasses\RuleBuilder;
use Grandadevans\GenerateForm\Handlers\PathHandler;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


/**
 * The Console Command for Grandadevans\laravel-form-validator
 *
 * Class FormGenerator
 *
 * @author  john Evans<john@grandadevans.com>
 * @licence https://github.com/GrandadEvans/laravel-form-validator/blob/master/LICENSE LICENSE MIT
 * @package Grandadevans\laravel-form-validator
 */
class FormGenerator {

    /**
     * The full path of the finished form
     *
     * @var string
     */
    protected $fullFormPath;

    /**
     * The namespace as specified by the user
     *
     * @var string
     */
    protected $namespace;

    /**
     * The classname as specified by the user (or defaults to Form)
     *
     * @var string
     */
    protected $className;

    /**
     * The full rules string as specified by the user
     *
     * @var string
     */
    protected $rulesString;

    /**
     * The directory of the finished form
     *
     * @var string
     */
    protected $dir;

    /**
     * Form Details
     */
    private $details;


    /**
     * @var PathHandler
     */
    private $pathHandler;

    /**
     * @var RuleBuilder
     */
    private $ruleBuilder;

    /**
     * @var OutputBuilder
     */
    private $outputBuilder;

	/**
	 * @var Filesystem
	 */
	private $filesystem;


    /**
     * Create a new command instance.
     *
     * @todo I still think this method needs a bit of work as I think it's "ugly"
     *
     * @param RuleBuilder   $ruleBuilder
     * @param PathHandler   $pathHandler
     * @param OutputBuilder $outputBuilder
     * @param Filesystem    $filesystem
     * @param array         $details
     *
     * @return array
     */
    public function generate(RuleBuilder $ruleBuilder, PathHandler $pathHandler, OutputBuilder $outputBuilder, Filesystem $filesystem, array $details)
    {
        $this->setDependancies($ruleBuilder, $pathHandler, $outputBuilder, $filesystem, $details);

	    // Get the full form path
	    $this->fullFormPath = $this->pathHandler->getFullPath($filesystem, $details);

        // If Force is false and the path exists then return with the error
        if (false === $details['force'] && false !== $this->pathHandler->doesPathExist($this->fullFormPath)) {
            return [
                'fullFormPath' => $this->fullFormPath,
                'result'       => 'fileExists'
            ];
        }

        $this->setFormAttributes($details);

        $buildResult = $this->attemptToBuildForm();

        return $buildResult;
    }


    /**
     * Set all of the form attributes to disk or persist in another way
     *
     * @param $details
     */
    private function setFormAttributes($details)
    {
        $this->dir         = $details['dir'];
        $this->className   = $details['className'];
        $this->namespace   = $details['namespace'];
        $this->rulesString = $details['rulesString'];
    }


    /**
     * Attempt to build the form
     *
     * @return string
     */
    private function attemptToBuildForm()
    {
        $rulesArray = $this->getRulesArrayFromRulesString($this->rulesString);

        return $this->buildOutput($rulesArray);
    }


    /**
     * Process the rules
     *
     * @param $rulesString
     *
     * @return mixed
     */
    public function getRulesArrayFromRulesString($rulesString)
    {
        return $this->ruleBuilder->buildRules($rulesString);
    }


    /**
     * Build the output
     *
     * @param $processedRules
     *
     * @return string
     */
    protected function buildOutput($processedRules)
    {
        $this->outputBuilder->build(
            $processedRules,
            $this->className,
            $this->namespace,
            $this->pathHandler->getFullPath($this->file, $this->details)
        );

        return $this->outputBuilder->getReturnDetails();

    }


    /**
     * Set the dependencies
     *
     * @param RuleBuilder   $ruleBuilder
     * @param PathHandler   $pathHandler
     * @param OutputBuilder $outputBuilder
     * @param Filesystem    $filesystem
     * @param array         $details
     */
    protected function setDependancies($ruleBuilder, $pathHandler, $outputBuilder, $filesystem, $details)
    {
        $this->pathHandler   = $pathHandler;
        $this->ruleBuilder   = $ruleBuilder;
        $this->outputBuilder = $outputBuilder;
        $this->file          = $filesystem;
        $this->details       = $details;
    }

}
