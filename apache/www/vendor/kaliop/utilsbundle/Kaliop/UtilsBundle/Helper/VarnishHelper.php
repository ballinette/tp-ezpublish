<?php


namespace Kaliop\UtilsBundle\Helper;

use FOS\HttpCacheBundle\CacheManager;
use Symfony\Component\Console\Output\OutputInterface;

class VarnishHelper {

    protected $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Purge cache key for specific values on every varnish server configured in ezpublish_env.yml
     * @param string $xKeyName name of the Cache Key (Ex : X-Location-Id, X-PropReference-Id, X-SolrData-Id)
     * @param array $cacheValues Cache elements ids (Ex: array(2,70,283) )
     */
    public function purgeKeys($xKeyName, array $cacheValues)
    {
        //Purge cache values with 200 items batches
        $countCacheValues = count($cacheValues);

        if ($countCacheValues > 200) {

            for ($offset = 0; $offset < $countCacheValues; $offset += 200) {
                $cacheValuesBatch = array_slice($cacheValues,$offset,200);
                $cacheValuesStr = implode('|',$cacheValuesBatch);
                $this->purgeCacheValues($xKeyName,$cacheValuesStr);
            }
        }
        else
        {
            $cacheValuesStr = implode('|',$cacheValues);
            $this->purgeCacheValues($xKeyName,$cacheValuesStr);
        }
    }

    /**
     * Curl request to send BAN url to varnish servers
     *
     * @param $xKeyName
     * @param $cacheValues
     */
    protected function purgeCacheValues($xKeyName, $cacheValues)
    {
        if(is_array($cacheValues))
        {
            $cacheValues = implode('|',$cacheValues);
        }
        $this->cacheManager->invalidate(array($xKeyName => $cacheValues));
    }

    public function purgeAll($xKeyName)
    {
        $this->cacheManager->invalidate(array($xKeyName => '*'));
    }

    /**
     * Warm up urls example : can be used to warm up Varnish cache by sending curl requests to each url
     * To be used in a sf command to be able to output
     * @param array $urls
     * @param OutputInterface $output
     */
    public function warmUpUrls(array $urls, OutputInterface $output)
    {

        foreach($urls as $i => $url)
        {
            $ch = curl_init();

            // set options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //timeout in seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER,         0);

            curl_exec($ch);
            curl_close($ch);

            if(fmod($i,100) == 0)
            {
                $output->writeln('Warmed up '.$i. ' urls in Varnish');
            }
        }
    }

} 