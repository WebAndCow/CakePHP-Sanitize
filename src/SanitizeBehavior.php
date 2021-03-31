<?php
App::uses('ModelBehavior', 'Model');

/**
 * SanitizeBehavior
 *
 * Nettoie les champs configurés des models avant l'enregistrement
 */
class SanitizeBehavior extends ModelBehavior
{
    /**
     * On initialise le mapping des fonctions pour les types de champ
     */
    private $mapping = [
        'string' => 'stripHtml',
        'text' => 'stripHtml'
    ];

    private $Model;

    /**
     * Configuration du behavior pour le model courant
     *
     * @param Model $Model Model using this behavior.
     * @param array $config Configuration options.
     * @return void
     */
    public function setup(Model $Model, $config = array())
    {
        $this->Model = $Model;

        if (!isset($this->settings[$Model->alias])) {

            if(is_array($config['fields'])) {
                $this->testFunctionExist(array_values($config['fields']));
            }

            if($config['fields'] == '*') {
                $config['fields'] = $this->getConfigFieldsForModel();
            }

            if (!empty($config['exclude'])) {
                $config['fields'] = array_diff_key($config['fields'], array_flip($config['exclude']));
            }

            if(!empty($config['map'])) {
                $this->testFunctionExist(array_values($config['map']));
                $this->setMapping($config['map']);
            }

            $this->settings[$Model->alias] = $config;

        }

    }

    /**
     * Modifie le mapping des fonctions pour les types de champ du model
     *
     * @param array $map
     * @return void
     */
    private function setMapping(array $map)
    {
        $this->mapping = $map;
    }

    /**
     * Test si une méthode existe dans la classe
     *
     * @param array $map
     * @return void
     * @throws Exception Si une méthode inexistante est appelée
     */
    private function testFunctionExist(array $map)
    {
        foreach ($map as $function) {

            if(!method_exists($this, $function)) {
                throw new Exception(__('La méthode %s est introuvable.', [$function]));
            }

        }

    }

    /**
     * Callback beforeSave.
     *
     * @param Model $Model Model sur lequel le save est appelé
     * @param array $options Options du save()
     * @return bool true.
     */
    public function beforeSave($Model, $options = array()): bool
    {

        foreach ($this->settings[$Model->alias]['fields'] as $field => $method) {
            
            if (!array_key_exists($field, $Model->data[$Model->alias]) || empty($Model->data[$Model->alias][$field])) {
                continue;
            }

            $Model->data[$Model->alias][$field] = call_user_func([$this, $method], $Model->data[$Model->alias][$field]);

        }

        return true;

    }

    /**
     * Construit le tableau de config pour les champs du model courant
     *
     * @return void
     */
    private function getConfigFieldsForModel()
    {

        $arrayFieldsWithFunction = [];

        foreach ($this->mapping as $type => $function) {
            
            $arrayFieldsWithFunction = array_merge(
                $arrayFieldsWithFunction,
                $this->replaceTypeByFunction($type, $function)
            );

        }

        return $arrayFieldsWithFunction;
    }

    /**
     * Remplace le type par la fonction dans le tableau des champs du Model
     *
     * @param string $type Type des champs à modifier
     * @param string $function Fonction qui remplace le type du champ
     * @return array
     */
    private function replaceTypeByFunction(string $type, string $function): array 
    {
        return array_fill_keys(array_keys($this->Model->getColumnTypes(), $type), $function);
    }


    /**
     * Retire toutes les balises HTML & PHP de la chaine de caractère
     * passée en attribut.
     *
     * @param string $string
     * @return string
     */
    private function stripHtml(string $string): string
    {
        return strip_tags($string);
    }

    /**
     * Suprimme les balises <img>, <script>, <style>, <link>. 
     *
     * @param string $string
     * @return string
     */
    private function stripScript(string $string): string
    {
        $regex =
            '/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|' .
            '<img[^>]*>|style="[^"]*")|' .
            '<script[^>]*>.*?<\/script>|' .
            '<style[^>]*>.*?<\/style>|' .
            '<!--.*?-->/is';

        $string = preg_replace($regex, '', $string);

        return trim($string);
    }



}
