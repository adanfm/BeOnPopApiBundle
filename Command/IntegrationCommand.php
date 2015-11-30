<?php

namespace Adanfm\BeOnPopApiBundle\Command;

use A2C\MonitorBundle\Entity\BeOnPop;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IntegrationCommand
 * @package Adanfm\BeOnPopApiBundle
 */
class IntegrationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('beonpop:integration')
            ->setDescription('Salva os dados na entity')
            ->addArgument(
                'entity',
                InputArgument::REQUIRED,
                'Qual entity vai ser salvo os dados retornados da API'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('[CRON] - Start '. date('d/m/Y H:i:s'));

        $entity = $input->getArgument('entity');

        $entityClass = $this->getContainer()->get('doctrine')->getRepository($entity)->getClassName();
        $beOnPopApi = $this->getContainer()->get('be_on_pop_api.api');

        $data = $beOnPopApi->api('pages');

        $date = new \DateTime();

        foreach ($data as $item) {
            $entityData = $this->getContainer()->get('doctrine')->getRepository('A2CMonitorBundle:BeOnPop')->findOneBy(array(
                'createdAt' => $date,
                'idPage'    => $item->idpage,
            ));

            if (is_null($entityData)) {


                $objEntity = new $entityClass();

                $objEntity
                    ->setIdPage($item->idpage)
                    ->setFanCount($item->fan_count)
                    ->setPops($item->pops)
                    ->setInfluence($item->influence)
                    ->setTimestamp($item->timestamp)
                    ->setProfile($item->profile)
                    ->setVariationPerc($item->variation_perc)
                    ->setVariationType($item->variation_type)
                    ->setPosts($item->posts)
                    ->setLikes($item->likes)
                    ->setComments($item->comments)
                    ->setShares($item->shares)
                ;

                $this->getContainer()->get('doctrine')->getManager()->persist($objEntity);
                $this->getContainer()->get('doctrine')->getManager()->flush();

                unset($objEntity);

                $output->writeln(sprintf('Inserindo dado do candidato [%s]',$item->name));
            }
        }

        $output->writeln('[CRON] - END '. date('d/m/Y H:i:s'));
    }
}