<?php

namespace Bleez\Correios\Helper;

class Data {

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /* !TODO colocar dimensoes no xml */

    /**
     * Dimensoes Caixa
     * @var array
     */
    protected $caixa = array(
        'minimo' => array(
            'largura' => 11,
            'altura' => 2,
            'comprimento' => 16
        ),
        'maximo' => array(
            'largura' => 105,
            'altura' => 105,
            'comprimento' => 105,
            'soma' => 200
        )
    );

    /**
     * Dimensoes Envelope
     * @var array
     */
    protected $envelope = array(
        'minimo' => array(
            'largura' => 11,
            'comprimento' => 16
        ),
        'maximo' => array(
            'largura' => 60,
            'comprimento' => 105
        )
    );

    /**
     * Dimensoes Cilindro
     * @var array
     */
    protected $cilindro = array(
        'minimo' => array(
            'largura' => 5,
            'comprimento' => 18
        ),
        'maximo' => array(
            'largura' => 91,
            'comprimento' => 105,
            'soma' => 200
        )
    );


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Retorna Limite de peso por serviço
     * !TODO Melhorar esse metodo
     * @return int
     */
    public function getLimitWeight(){
        $types = explode(',', $this->_scopeConfig->getValue('carriers/correios/types'));

        $weight10 = array('40878', '40290', '40886', '40215', '40169');

        if(count(array_intersect($weight10, $types)) > 0){
            return 10;
        }

        $weight15 = '81019';

        if(in_array($weight15, $types)){
            return 15;
        }

        return 30;
    }

    /**
     * Retorna limites por dimensões
     * @return string
     */
    public function getLimitSizes(){
        switch ($this->_scopeConfig->getValue('carriers/correios/format')) {
            case 1:
                return json_encode($this->envelope);
            case 3:
                return json_encode($this->cilindro);
            default:
                return json_encode($this->caixa);
        }
    }

    /**
     * Retorna Formato do pacote
     * !TODO Melhorar esse metodo
     * @return string
     */
    public function getFormat(){
        switch ($this->_scopeConfig->getValue('carriers/correios/format')) {
            case 1:
                return 'envelope';
            case 3:
                return 'cilindro';
            default:
                return 'caixa';
        }
    }

    /**
     * Remove array that already exists
     * @param $array
     * @param $key
     * @return array
     */
    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    /**
     * @return mixed
     */
    public function getPostcodeFromStore()
    {
        return str_replace('-', '',$this->_scopeConfig->getValue('general/store_information/postcode'));
    }

    /**
     * @param $serviceId
     * @return bool|mixed
     */
    public function loadServiceById($serviceId)
    {
        $allServices = json_decode($this->_scopeConfig->getValue('carriers/correios/servicesnames'));
        $searchById = array_search($serviceId, array_column($allServices, 'id'));

        if ($searchById === false) {
            return false;
        }

        return $allServices[$searchById];
    }
}
