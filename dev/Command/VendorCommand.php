<?php

namespace Oxygen\Dev\Command;

use Github\Api\Repo;
use Github\Client as GithubClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class VendorCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('vendor')
            ->setDescription("Check core library vendors for new commits")
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, "GitHub username. Recommended if hitting GitHub API rate limit.")
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, "GitHub password.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        chdir(__DIR__.'/../..');

        $table = new TableHelper();

        $table->setHeaders(['Vendor name', 'Ahead', 'Permalink']);

        $vendorRows = array_filter(file('vendors.csv')); // Filter out empty rows.
        $githubApi = new GithubClient();
        /** @var Repo $repoApi */
        $repoApi = $githubApi->api('repo');

        if ($input->getOption('username')) {
            $password = $input->getOption('password');
            if (!$password) {
                $q = new Question(sprintf('Please enter the GitHub password for user <comment>%s</comment>:', $input->getOption('username')));
                $q->setHidden(true);
                $q->setValidator(function ($value) {
                    if (empty($value)) {
                        throw new \InvalidArgumentException('You must provide a password');
                    }
                });
                $password = $this->getHelper('question')->ask($input, $output, $q);
            }
            $githubApi->authenticate($input->getOption('username'), $password, GithubClient::AUTH_HTTP_PASSWORD);
        }

        foreach ($vendorRows as $vendorRow) {
            list($vendorName, $repoId, $latestCommitHash) = str_getcsv($vendorRow);
            // Repo ID is in the format author-name/repository-name.
            list($repoAuthor, $repoName) = explode('/', $repoId);

            // This provides us with much information, example: http://pastebin.com/raw.php?i=gkmUS9nU
            $headInfo = $repoApi->commits()->compare($repoAuthor, $repoName, $latestCommitHash, 'HEAD');

            $aheadBy = "<comment>Up to date!</comment>";
            $permalink = "";

            if ($headInfo['ahead_by']) {
                $permalink = $headInfo['permalink_url'];

                $additions = array_sum(array_map(function ($files) {
                    return $files['additions'];
                }, $headInfo['files']));
                $deletions = array_sum(array_map(function ($files) {
                    return $files['deletions'];
                }, $headInfo['files']));

                if ($additions) {
                    $additions = "<comment>+$additions</comment>";
                }

                if ($deletions) {
                    $deletions = "<info>-$deletions</info>";
                }

                $aheadBy = "<info>{$headInfo['ahead_by']}</info> commits ($additions/$deletions)";
            }
            $table->addRow(["$vendorName (<info>$repoId</info>)", $aheadBy, $permalink]);
        }

        $table->render($output);
    }
}
