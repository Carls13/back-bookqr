<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

class AppCreateEntityCommand extends Command
{
    protected static $defaultName = 'app:create-entity';

    protected $singular;
    protected $plural;
    protected $controllerType;
    protected $fileTypes;
    protected $basePath;
    protected $helper;
    protected $fileSystem;
    protected $finder;
    protected $app;

    public function __construct($name = null, KernelInterface $kernel)
    {
        $this->basePath = $kernel->getRootDir();
        $this->fileTypes = [
            'entity',
            'transformer',
            'controller',
            'fixture',
            'test',
        ];
        $this->fileSystem = new Filesystem();
        $this->finder = new Finder();
        $this->app = new Application($kernel);
        $this->app->setAutoExit(false);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates all files required for an entity')
            ->addArgument('name', InputArgument::REQUIRED, 'Entity name in singular form')
            ->addArgument('controllerType', InputArgument::REQUIRED, 'Choose the type of controller = admin|front|internal|unauthenticated')
            ->addArgument('plural', InputArgument::OPTIONAL, 'Plural name for the entity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->singular = $this->toLowercase($input->getArgument('name'));
        $this->plural = $this->toLowercase($input->getArgument('plural'));
        $this->controllerType = $this->toLowercase($input->getArgument('controllerType'));
        $this->helper = $this->getHelper('question');

        if (!in_array($this->controllerType, ['admin', 'front', 'internal', 'unauthenticated'])) {
            $io->error('controllerType must be admin, front, internal or unauthenticated');
            return;
        }

        if (!$this->plural) {
            $questionText = $this->getQuestionIfPluralIsNotDefined();
            $question = new ConfirmationQuestion($questionText, false);

            if ($this->helper->ask($input, $output, $question)) {
                $this->plural = $this->toPlural($this->singular);
            } else {
                $io->error('Add --plural parameter to specify custom name');
                return;
            }
        }

        //create a file for each $fileType
        foreach($this->fileTypes as $fileType) {

            if ($fileType == 'entity') {
                $result = $this->createEntity();
                $io->writeln($result);
                continue;
            }

            $path = $this->getDestinationPath($fileType);

            $stub = $this->readStubFile($fileType);
            $content = $this->replaceStubVariables($stub);

            $result = $this->saveClassFile($path, $content);

            if ($result) {
                $io->success($fileType .' created successfully.');
            } else {
                $io->error('Cannot create ' . $fileType);
            }

        }

    }

    /**
     * Transform to lowercase
     * @param  string $string
     * @return string
     */
    private function toLowercase($string = '')
    {
        return strtolower($string);
    }

    /**
     * Get question if plural is not defined
     * @return string
     */
    private function getQuestionIfPluralIsNotDefined()
    {
        $singular = $this->singular;
        $plural = $this->toPlural($singular);

        $question = <<< EOT
Do you want to use this entity names?
singular="$singular"
plural="$plural"
(y/n) 
EOT;

        return $question;
    }

    /**
     * Transform singular word to plural and lowercase
     * @param  string $string
     * @return string
     */
    private function toPlural($string = '')
    {
        $plural = Inflector::pluralize($string);
        return $this->toLowercase($plural);
    }

    /**
     * Transform word to camelcase
     * @param  string $string
     * @return string
     */
    private function camelize($string = '')
    {
        return ucwords(Inflector::camelize($string));
    }

    /**
     * Get destination path based on entity name
     * @param  string $fileType
     * @return string
     */
    private function getDestinationPath($fileType = '')
    {
        $singular = $this->camelize($this->singular);
        $plural = $this->camelize($this->plural);
        $controllerType = $this->camelize($this->controllerType);
        $path = $this->basePath . '/';

        switch ($fileType) {
            case 'entity':
                $path .= 'src/Entity/' . $singular;
                break;

            case 'controller':
                $path .= 'Controller/' . $controllerType . '/' . $plural . 'Controller';
                break;

            case 'transformer':
                $path .= 'Transformers/' . $singular . 'Transformer';
                break;

            case 'fixture':
                $path = 'src/DataFixtures/' . $plural . 'Fixture';
                break;

            case 'test':
                $path = 'tests/' . $singular . 'ControllerTest';
                break;
        }

        $path .= '.php';

        return $path;
    }

    /**
     * Read stub file
     * @param  string $fileType
     * @return string
     */
    private function readStubFile($fileType = '')
    {
        $content = '';

        $dir = $this->basePath . '/Command/stubs/';
        $fileName = $this->camelize($fileType) . '.stub';

        if ($this->finder->files()->in($dir)->name($fileName)) {
            foreach ($this->finder as $file) {
                if ($file->getRelativePathname() != $fileName) {
                    continue;
                }

                $content = $contents = $file->getContents();
            }
        }

        return $content;
    }

    /**
     * Replace stub variables
     * @param  string $content
     * @return string
     */
    private function replaceStubVariables($content = '')
    {
        $singular = $this->singular;
        $plural = $this->plural;
        $controllerType = $this->controllerType;

        $meanings = [
            '{{SingularLowercased}}' => $this->toLowercase($singular),
            '{{PluralLowercased}}' => $this->toLowercase($plural),
            '{{ControllerTypeLowercased}}' => $this->toLowercase($controllerType),
            '{{SingularCamelized}}' => $this->camelize($singular),
            '{{PluralCamelized}}' => $this->camelize($plural),
            '{{ControllerTypeCamelized}}' => $this->camelize($controllerType),
        ];

        foreach ($meanings as $variable => $value) {
            $content = str_replace($variable, $value, $content);
        }

        return $content;
    }

    /**
     * Save class files with contents
     * @param  string $path
     * @param  string $content
     * @return bool
     */
    private function saveClassFile($path = '', $content = '')
    {
        if (!$this->fileSystem->exists($path) && $content) {
            try {
                $this->fileSystem->dumpFile($path, $content);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates an entity and repository by calling the make:entity command
     * @return string
     */
    private function createEntity()
    {
        $input = new ArrayInput([
            'command' => 'make:entity',
            'name' => $this->singular,
            '-n' => true,
        ]);

        $output = new BufferedOutput();
        $this->app->run($input, $output);

        // return the output, don't use if you used NullOutput()
        return $output->fetch();
    }

}
