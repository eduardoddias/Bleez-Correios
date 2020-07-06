<?php

namespace Bleez\Correios\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use PhpSigep\Model\Dimensao;
use PhpSigep\Model\CalcPrecoPrazo;
use PhpSigep\Services\SoapClient;
use PhpSigep\Model\AccessDataHomologacao;
use PhpSigep\Model\ServicoDePostagem;
use PhpSigep\Model\ServicoAdicional;
use PhpSigep\Services\SoapClient\Real;
use PhpSigep\Bootstrap;
use PhpSigep\Model\AccessData;
use Symfony\Component\Config\Definition\Exception\Exception;
use DVDoug\BoxPacker\Packer;
use DVDoug\BoxPacker\ItemList;
use DVDoug\BoxPacker\VolumePacker;
use Bleez\Correios\Model\BoxPacker\Item;
use Bleez\Correios\Model\BoxPacker\Box;


/**
 * Class Correios
 * Shipping method Correios
 * @package Bleez\Correios\Model\Carrier
 */
class Correios extends \Magento\Shipping\Model\Carrier\AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'correios';

    /**
     * @var int
     */
    protected $_qtdFretes = 1;

    /**
     * @var \Bleez\Correios\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Bleez\Correios\Helper\Sigep
     */
    protected $_heperSigep;

    /**
     * Correios constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Shipping\Helper\Carrier $carrierHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Module\Dir\Reader $configReader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\Math\Division $mathDivision
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Bleez\Correios\Model\Tracker\Request $trackerRequest
     * @param \Bleez\Correios\Helper\Data $helper
     * @param \Bleez\Correios\Helper\Sigep $helperSigep
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Bleez\Correios\Model\Tracker\Request $trackerRequest,
        \Bleez\Correios\Helper\Data $helper,
        \Bleez\Correios\Helper\Sigep $helperSigep,
        array $data = []
    ) {
        $this->readFactory = $readFactory;
        $this->_carrierHelper = $carrierHelper;
        $this->_coreDate = $coreDate;
        $this->_storeManager = $storeManager;
        $this->_configReader = $configReader;
        $this->string = $string;
        $this->mathDivision = $mathDivision;
        $this->_dateTime = $dateTime;
        $this->_httpClientFactory = $httpClientFactory;
        $this->trackerRequest = $trackerRequest;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_helper = $helper;
        $this->_helperSigep = $helperSigep;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['correios' => $this->getConfigData('name')];
    }


    /**
     * Retorna fretes para o magento
     * @param RateRequest $request
     * @return bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        try{
            //Inicia Lib
            $this->_initSigep();

            $quote = $this->getQuote($request->getAllItems());

            if(!$quote) return false;

            $services = $this->_getServices($request, $quote);

            $result = $this->_rateResultFactory->create();

            $allowed_services = explode(',', $this->getConfigData('types'));

            $shippingRates = array();

            foreach ($services as $s) {
                foreach($s->getResult() as $service) {
                    if ($service->getErroCodigo() == 0 && in_array((string)$service->getServico()->getCodigo(), $allowed_services)) {
                        if ($this->getConfigData('free_shipping_enabled') && $this->getConfigData('free_shipping_only') && $service->getServico()->getCodigo() != $this->getConfigData('free_shipping_service')) {
                            continue;
                        }
                        if ($this->getConfigData('free_shipping_enabled') && $service->getServico()->getCodigo() == $this->getConfigData('free_shipping_service') && $request->getFreeShipping()) {
                            $shippingRates[$service->getServico()->getCodigo()]["valor"] = 0;
                            $shippingRates[$service->getServico()->getCodigo()]["prazo"] = $this->_calculateShippingDays($service);
                            $shippingRates[$service->getServico()->getCodigo()]["nome"] = $service->getServico()->getNome();
                        } else {
                            if (!isset($shippingRates[$service->getServico()->getCodigo()])) {
                                $shippingRates[$service->getServico()->getCodigo()]["valor"] = 0;
                                $shippingRates[$service->getServico()->getCodigo()]["prazo"] = 0;
                                $shippingRates[$service->getServico()->getCodigo()]["nome"] = $service->getServico()->getNome();
                            }
                            $loadedService = $this->_helper->loadServiceById($service->getServico()->getCodigo());

                            $priceService = $service->getValor();
                            if (isset($loadedService->fee) && !empty($loadedService->fee)) {
                                $priceService += $priceService * ($loadedService->fee / 100);
                            }

                            $shippingRates[$service->getServico()->getCodigo()]["valor"] += $priceService;

                            if ($this->_calculateShippingDays($service) > $shippingRates[$service->getServico()->getCodigo()]["prazo"]) {
                                $shippingRates[$service->getServico()->getCodigo()]["prazo"] = $this->_calculateShippingDays($service);
                            }
                        }
                    }
                }
            }

            foreach ($shippingRates as $cod => $rate){
                $method = $this->_rateMethodFactory->create();
                $method->setCarrier('correios');
                $method->setCarrierTitle($this->getConfigData('name'));
                $method->setMethod($rate["nome"]);

                if($rate["valor"] == 0){ //free shipping
                    $method->setMethodTitle($this->getConfigData('free_shipping_text') . sprintf($this->getConfigData('text_days'), $this->_formatShippingDays($rate["prazo"])));
                    $method->setPrice(0);
                    $method->setCost(0);
                } else {
                    $method->setMethodTitle($this->_getServiceName($cod) . sprintf($this->getConfigData('text_days'), $this->_formatShippingDays($rate["prazo"])));
                    $method->setPrice($rate["valor"]);
                    $method->setCost($rate["valor"]);
                }

                $method->setMethodDescription('Quantidade de fretes: '.$this->_qtdFretes);
                $result->append($method);
            }

            return $result;
        }catch(Exception $e){
            $this->_logger->error($e->getMessage());
            return false;
        }

    }

    protected function _getServices($request, $quote){
        $services = array();
        $limite = json_decode($this->_helper->getLimitSizes(), true);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface')->get($item->getSku());

            $originPostcode = (int) str_replace('-', '', $product->getOriginPostcode());
            $altura = $product->getAltura();
            $comprimento = $product->getComprimento();
            $largura = $product->getLargura();
            $peso = $product->getWeight();

            if(!$altura || !$comprimento || !$largura || !$peso) return [];

            if(!$this->_verificaTamanho($altura, $largura, $comprimento, $limite) || !$this->_verificaPeso($peso)){
                return $services;
            }

            // If product does not have origin postcode, we take it from store.
            if (!$originPostcode) {
                $originPostcode = (int)$this->_helper->getPostcodeFromStore();
            }

            for($i = 0; $i < $item->getQty(); $i++ ){
                $pacotes[] = array(
                    "altura"      => $altura,
                    "largura"     => $largura,
                    "comprimento" => $comprimento,
                    "peso"        => $peso * 100, //converto em g
                    "origin_postcode" => $originPostcode
            );
            }
        }

        //Divisão do frete
        if($this->getConfigData('divisao_frete') && $this->getConfigData('format') != 3){
            $numPacotes = count($pacotes);

            $countPacote = 0;
            do {
                $temPacotes = false;

                $box[] = new Box(
                    'Pacote',
                    $limite["maximo"]["altura"],
                    $limite["maximo"]["largura"],
                    $limite["maximo"]["comprimento"],
                    0,
                    $limite["maximo"]["altura"] - 5,
                    $limite["maximo"]["largura"] - 5,
                    $limite["maximo"]["comprimento"] - 5,
                    $this->_helper->getLimitWeight() * 100 //converto em kg
                );

                $items = new ItemList();

                for($i = 0; $i < $numPacotes; $i++) {
                    if(!isset($pacotes[$i])) continue; //o pacote ja foi inserido

                    $itemsAnterior = clone $items;
                    $items->insert(new Item('Item '.$i, $pacotes[$i]["altura"], $pacotes[$i]["largura"], $pacotes[$i]["comprimento"], ceil($pacotes[$i]["peso"]), false, $pacotes[$i]['origin_postcode']));
                    $volumePacker = new VolumePacker(end($box), $items);
                    $packedBox = $volumePacker->pack();

                    //verificações correios
                    $ultrapassaMedidas = $packedBox->getUsedWidth() +
                        $packedBox->getUsedLength() +
                        $packedBox->getUsedDepth() +
                        $pacotes[$i]["altura"] +
                        $pacotes[$i]["largura"] +
                        $pacotes[$i]["comprimento"] >
                        $limite["maximo"]["soma"];

                    $ultrapassaPeso = $packedBox->getWeight() + $pacotes[$i]["peso"] >
                        $this->_helper->getLimitWeight() * 100;

                    //deu errado, volto o estado anterior e continuo o loop
                    if($ultrapassaMedidas || $ultrapassaPeso) {
                        $items = clone $itemsAnterior;
                    }
                }

                //fecho o pacote
                $finalPackedBox[] = clone $packedBox;

                //removo os itens que foram empacotados para checar se faltou algum pacote
                $packedItems = $packedBox->getItems();
                foreach ($packedItems as $packedItem) {
                    $indexItem = (int) str_replace('Item ', '', $packedItem->getItem()->getDescription());
                    unset($pacotes[$indexItem]);
                }

                if(count($pacotes) > 0) {
                    $temPacotes = true;
                }

                $countPacote++;

            } while($temPacotes);
        }

        if(isset($finalPackedBox)){
            $pacotes = array();
            foreach ($finalPackedBox as $b){
                // Iterator on items because origin postcode
                foreach ($b->getItems() as $item) {
                    $pacotes[] = array(
                        "altura" => $b->getUsedWidth(),
                        "largura" => $b->getUsedLength(),
                        "comprimento" => $b->getUsedDepth(),
                        "peso" => $b->getWeight() / 100,
                        "origin_postcode" => $item->getItem()->getOriginPostcode()
                    );
                }
            }
        }

        $this->_qtdFretes = $countPacote;

        //Remove packages thats contains origin postcode duplicated
        $pacotes = $this->_helper->unique_multidim_array($pacotes, 'origin_postcode');

        foreach ($pacotes as $pacote){
            $dimensions = $this->_createDimensionsNew($pacote["altura"], $pacote["largura"], $pacote["comprimento"]);
            $services[] = $this->_getService($request, $dimensions, $pacote["peso"], $pacote['origin_postcode']);
        }

        return $services;
    }

    /**
     * Retorna servicos dos correios
     * @param RateRequest $request
     * @param \PhpSigep\Model\Dimensao $dimensions
     * @return \PhpSigep\Services\Result
     */
    protected function _getService(RateRequest $request, Dimensao $dimensions, $peso, $originPostcode){
        $params = new CalcPrecoPrazo();
        $params->setAccessData(new AccessDataHomologacao());
        $params->setCepOrigem($originPostcode);
        $params->setCepDestino($request->getDestPostcode());
        $params->setServicosPostagem(ServicoDePostagem::getAll());
        $params->setAjustarDimensaoMinima(true);
        $params->setDimensao($dimensions);

        $servicosAdicionais = array();

        if ($this->getConfigData('aviso_recebimento')) {
            $avisoDeRecebimento = new ServicoAdicional();
            $avisoDeRecebimento->setCodigoServicoAdicional(ServicoAdicional::SERVICE_AVISO_DE_RECEBIMENTO);
            $servicosAdicionais[] = $avisoDeRecebimento;
        }

        if ($this->getConfigData('mao_propria')) {
            $maoPropria = new ServicoAdicional();
            $maoPropria->setCodigoServicoAdicional(ServicoAdicional::SERVICE_MAO_PROPRIA);
            $servicosAdicionais[] = $maoPropria;
        }

        if ($this->getConfigData('valor_declarado') && $request['package_value']) {
            $valorDeclarado = new ServicoAdicional();
            $valorDeclarado->setCodigoServicoAdicional(ServicoAdicional::SERVICE_VALOR_DECLARADO);
            $valorDeclarado->setValorDeclarado($request['package_value']);
            $servicosAdicionais[] = $valorDeclarado;
        }

        if($this->getConfigData('ect') && $this->getConfigData('password')){

            $accessData = new AccessData();
            $accessData->setUsuario($this->getConfigData('ect'));
            $accessData->setSenha($this->getConfigData('password'));
            $accessData->setCodAdministrativo($this->getConfigData('codigoadm'));
            $params->setAccessData($accessData);

        }

        $params->setServicosAdicionais($servicosAdicionais);
        //$params->setPeso($this->_calculateWeightShipping($request));
        $params->setPeso($peso);

        $phpSigep = new Real();
        $this->_logger->debug(print_r($params, true));
        return $phpSigep->calcPrecoPrazo($params);
    }

    /**
     * Soma prazo de entrega do servico e coloca adicionais
     * @param $service
     * @return int
     */
    protected function _formatShippingDays($days){
        if($this->getConfigData('servicesnames')){
            return (int)$days+(int)$this->getConfigData('add_days');
        }
        return '';
    }

    protected function _createDimensionsNew($altura, $largura = null, $comprimento){
        $dimensao = new Dimensao();
        $dimensao->setTipo($this->getConfigData('format'));

        if($this->getConfigData('format') == 3){
            //Cilindro
            $dimensao->setDiametro($largura);
            $dimensao->setComprimento($comprimento);
        }else{
            //Caixa e envelope
            $dimensao->setAltura($altura);
            $dimensao->setComprimento($comprimento);
            $dimensao->setLargura($largura);
        }
        return $dimensao;
    }

    protected function _verificaTamanho($altura, $largura = null, $comprimento, $limite){
        if( ($altura <= ($limite["maximo"]["largura"]) && // Cilindro
                $comprimento <= ($limite["maximo"]["comprimento"]) &&
                $this->getConfigData('format') == 3) ||
            ($largura <= ($limite["maximo"]["altura"]) && //Caixa e envelope
                $altura <= ($limite["maximo"]["largura"]) &&
                $comprimento <= ($limite["maximo"]["comprimento"]) &&
                $altura + $largura + $comprimento <= ($limite["maximo"]["soma"]) &&
                $this->getConfigData('format') != 3) ){
            return true;
        }

        return false;

    }

    protected function _verificaPeso($peso){
        if($peso > $this->_helper->getLimitWeight()){
            return false;
        }
        return true;
    }

    /**
     * Inicia Lib Sigep
     */
    protected function _initSigep(){
        $this->_helperSigep->_initSigep();
    }

    /**
     * Seta e retorna dimensoes do pacote
     * @param RateRequest $request
     * @return \PhpSigep\Model\Dimensao
     */
    protected function _createDimensions(RateRequest $request){

        $dimensao = new Dimensao();
        $dimensao->setTipo($this->getConfigData('format'));

        $largura = 0;
        $altura = 0;
        $comprimento = 0;

        $limite = json_decode($this->_helper->getLimitSizes(), true);

        foreach($request->getAllItems() as $item){

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProduct()->getId());

            $largura += $product->getLargura() * $item->getQty();
            $altura += $product->getAltura() * $item->getQty();
            $comprimento += $product->getComprimento() * $item->getQty();

            /**
             * Verifica se as dimensões do produto cabem no mesmo pacote
             */
            for($pacotes = 1; $pacotes <= $item->getQty(); $pacotes++){
                if( ($altura <= ($limite["maximo"]["largura"] * $pacotes) && // Cilindro
                        $comprimento <= ($limite["maximo"]["comprimento"] * $pacotes) &&
                        $this->getConfigData('format') == 3) ||
                    ($largura <= ($limite["maximo"]["altura"] * $pacotes) && //Caixa e envelope
                        $altura <= ($limite["maximo"]["largura"] * $pacotes) &&
                        $comprimento <= ($limite["maximo"]["comprimento"] * $pacotes) &&
                        $altura + $largura + $comprimento <= ($limite["maximo"]["soma"] * $pacotes) &&
                        $this->getConfigData('format') != 3) ){
                    $this->_qtdFretes = $pacotes;

                    $largura /= $pacotes;
                    $altura /= $pacotes;
                    $comprimento /= $pacotes;

                    break;
                }
            }
        }

        if($this->getConfigData('format') == 3){
            //Cilindro
            $dimensao->setDiametro($largura);
            $dimensao->setComprimento($comprimento);
        }else{
            //Caixa e envelope
            $dimensao->setAltura($altura);
            $dimensao->setComprimento($comprimento);
            $dimensao->setLargura($largura);
        }

        return $dimensao;
    }



    /**
     * Retorna servicos dos correios
     * @param RateRequest $request
     * @param \PhpSigep\Model\Dimensao $dimensions
     * @return \PhpSigep\Services\Result
     */

    protected function _getServicos(RateRequest $request, Dimensao $dimensions)
    {
        $params = new CalcPrecoPrazo();
        $params->setAccessData(new AccessDataHomologacao());
        $params->setCepOrigem($request->getPostcode());
        $params->setCepDestino($request->getDestPostcode());
        $params->setServicosPostagem(ServicoDePostagem::getAll());
        $params->setAjustarDimensaoMinima(true);
        $params->setDimensao($dimensions);

        $servicosAdicionais = array();

        if ($this->getConfigData('aviso_recebimento')) {
            $avisoDeRecebimento = new ServicoAdicional();
            $avisoDeRecebimento->setCodigoServicoAdicional(ServicoAdicional::SERVICE_AVISO_DE_RECEBIMENTO);
            $servicosAdicionais[] = $avisoDeRecebimento;
        }

        if ($this->getConfigData('mao_propria')) {
            $maoPropria = new ServicoAdicional();
            $maoPropria->setCodigoServicoAdicional(ServicoAdicional::SERVICE_MAO_PROPRIA);
            $servicosAdicionais[] = $maoPropria;
        }

        if ($this->getConfigData('valor_declarado') && $request['package_value']) {
            $valorDeclarado = new ServicoAdicional();
            $valorDeclarado->setCodigoServicoAdicional(ServicoAdicional::SERVICE_VALOR_DECLARADO);
            $valorDeclarado->setValorDeclarado($request['package_value']);
            $servicosAdicionais[] = $valorDeclarado;
        }

        if($this->getConfigData('ect') && $this->getConfigData('password')){

            $accessData = new AccessData();
            $accessData->setUsuario($this->getConfigData('ect'));
            $accessData->setSenha($this->getConfigData('password'));
            $accessData->setCodAdministrativo($this->getConfigData('codigoadm'));
            $params->setAccessData($accessData);

        }

        $params->setServicosAdicionais($servicosAdicionais);
        $params->setPeso($this->_calculateWeightShipping($request));

        $phpSigep = new Real();
        $this->_logger->debug(print_r($params, true));
        return $phpSigep->calcPrecoPrazo($params);
    }

    /**
     * Soma prazo de entrega do servico e coloca adicionais
     * @param $service
     * @return int
     */
    protected function _calculateShippingDays($service){
        if($this->getConfigData('servicesnames')){
            return (int)$service->getPrazoEntrega()+(int)$this->getConfigData('add_days');
        }
        return '';
    }

    /**
     * Divide o frete se passar do limite de peso
     * @param RateRequest $request
     * @return float|string
     */
    protected function _calculateWeightShipping(RateRequest $request){
        if($this->getConfigData('divisao_frete')) {

            /**
             * Se algum item for maior que o limite do correios retorna o peso sem divisão
             */
            foreach($request->getAllItems() as $item){
                /**
                 * @var $item \Magento\Quote\Model\Quote|Item
                 */
                if($item->getWeight() > $this->_helper->getLimitWeight()){
                    $this->_logger->debug('1Peso: '.$request->getPackageWeight());
                    return $request->getPackageWeight();
                }
            }

            /**
             * Divide o peso pela quantidade de itens
             */
            if($request->getPackageQty() > $this->_qtdFretes){
                if ($request->getPackageWeight() > $this->_helper->getLimitWeight() || $request->getPackageQty() > 1) {
                    $shipWeight = 0;
                    for ($k = 1; $k <= $request->getPackageQty(); $k++) {
                        if ($request->getPackageWeight() / $k <= $this->_helper->getLimitWeight()) {
                            $shipWeight = $request->getPackageWeight() / $k;
                            /**
                             * Verifico se a divisão de pacotes pelas dimensões é maior que a do peso
                             */
                            if($this->_qtdFretes < $k){
                                $this->_qtdFretes = $k;
                            }
                            break;
                        }
                    }
                }
            }else{
                $shipWeight = $request->getPackageWeight() / $this->_qtdFretes;
            }

            if ($shipWeight > 0) {
                $this->_logger->debug('2Peso: '.number_format($shipWeight, 2));
                return number_format($shipWeight, 2);
            }
        }

        $this->_logger->debug('3Peso: '.$request->getPackageWeight());
        return $request->getPackageWeight();
    }

    /**
     * @param float $valor
     * @return float
     */
    protected function _calculatePrice($valor){
        return ($valor*$this->_qtdFretes)+(float)$this->getConfigData('add_tax');
    }

    protected function _getServiceName($codigo){
        $names = $this->getConfigData('servicesnames');
        foreach(json_decode($names) as $name){
            if($name->id == $codigo){
                return $name->name;
            }
        }
    }

    /* Tracking */

    /**
     * @return bool
     */

    public function isTrackingAvailable(){
        return true;
    }

    /**
     * @param $trackings
     * @return array
     */
    public function getTracking($trackings)
    {
        return array('1', '2');
    }

    /**
     * @param string $number
     * @return \Bleez\Correios\Model\Tracker\Request
     */
    public function getTrackingInfo($number){

        $data = $this->trackerRequest->send($number);

        $tracking = $this->_trackStatusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle($this->getConfigData('name'));
        $tracking->setTracking($number);
        $tracking->setProgressdetail($data);

        return $tracking;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return null;
     */

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->setRequest($request);
    }

    protected function getQuote($items){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($items  as $item)
        {
            return $objectManager->get('Magento\Quote\Model\QuoteFactory')->create()->load($item->getQuoteId());
        }
        return false;
    }

}