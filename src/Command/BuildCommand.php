<?php
declare(strict_types=1);

namespace App\Command;

use App\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Twig\Environment;

#[AsCommand('site:build')]
class BuildCommand extends Command
{
    private Environment $twig;
    private Parser $parser;
    public const string ROOT_DIR = __DIR__ . '/../..';

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
        $this->parser = new Parser();

        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this->addOption('watch', 'w', InputOption::VALUE_NONE, 'Watch for changes and rebuild automatically');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $watch = $input->getOption('watch');

        $this->build($output);

        if ($watch) {
            $output->writeln('<info>Watching for changes... Press Ctrl+C to stop.</info>');
            $this->watch($output);
        }

        return Command::SUCCESS;
    }

    private function build(OutputInterface $output): void
    {
        $output->writeln('<comment>Building site...</comment>');
        $this->createPages();
        $this->createPosts();
        $output->writeln('<info>Build complete!</info>');
    }

    private function watch(OutputInterface $output): void
    {
        $lastModified = $this->getLastModifiedTime();

        while (true) {
            sleep(1);
            $currentModified = $this->getLastModifiedTime();

            if ($currentModified > $lastModified) {
                $output->writeln('<comment>Change detected, rebuilding...</comment>');
                $this->build($output);
                $lastModified = $currentModified;
            }
        }
    }

    private function getLastModifiedTime(): int
    {
        $latestTime = 0;
        $directories = [
            self::ROOT_DIR . '/data/_pages',
            self::ROOT_DIR . '/data/_posts',
            self::ROOT_DIR . '/templates',
            self::ROOT_DIR . '/assets',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $finder = Finder::create()->files()->in($directory);
            foreach ($finder as $file) {
                $mtime = $file->getMTime();
                if ($mtime > $latestTime) {
                    $latestTime = $mtime;
                }
            }
        }

        return $latestTime;
    }

    private function createPages(): void
    {
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_pages') as $page) {
            $data = $this->parser->parse($page->getContents());

            if (isset($data['layout'])) {
                $posts = $this->fetchPosts($data['layout']);
                $data['posts'] = $posts;
            }

            $template = $data['template'] ?? 'page.html.twig';
            $html = $this->twig->render($template, $data);

            file_put_contents(self::ROOT_DIR . '/public/' . $data['permalink'], $html);
        }
    }

    private function createPosts(): void
    {
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $data = $this->parser->parse($file->getContents());
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data['created_at'] = new \DateTime($matches[0]);

            $html = $this->twig->render('post/post.html.twig', $data);
            $filename = str_replace('md', 'html', $file->getFilename());
            $folder = self::ROOT_DIR . '/public/posts/';

            if (!@mkdir($folder) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
            }

            file_put_contents($folder . $filename, $html);
        }
    }

    private function fetchPosts(?string $category): array
    {
        $posts = [];
        /** @var SplFileInfo $file */
        foreach (Finder::create()->in(self::ROOT_DIR . '/data/_posts') as $file) {
            $regex = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
            preg_match($regex, $file->getRelativePathname(), $matches);

            $data = $this->parser->parse($file->getContents());
            $filename = str_replace('md', 'html', $file->getFilename());

            if (null !== $category && ($data['layout'] ?? null) !== $category) {
                continue;
            }

            $posts[] = [
                'title' => $data['title'],
                'created_at' => new \DateTime($matches[0]),
                'permalink' => '/posts/'.$filename
            ];
        }

        return $posts;
    }
}
