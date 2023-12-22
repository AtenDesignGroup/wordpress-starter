<?php
if (!class_exists("wd_ANInputs")) {
    /**
     * Class wd_ANInputs
     *
     * AlphaNumeric inputs class - Prints N number of alphanumeric inputs (for paddings, margins etc..)
     * Stores the values space separated
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2018, Ernest Marcinko
     */
    class wd_ANInputs extends wpdreamsType {
        private $args = array(
            'inputs'        => array(
                array('Top', '0'),
                array('Right', '0'),
                array('Bottom', '0'),
                array('Left', '0')
            ),
            'input_class'   => 'threedigit',
            'input_default' => '0',
        );
        private $inputs = array();

        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class="wd_an_inputs" id='wd_an_inputs-<?php echo self::$_instancenumber; ?>'>
                <fieldset>
                    <legend><?php echo $this->label; ?></legend>
                    <?php foreach ($this->inputs as $k => $value): ?>
                        <label>
                            <?php echo isset($this->args['inputs'][$k], $this->args['inputs'][$k][0]) ? $this->args['inputs'][$k][0] : ''; ?>
                            <input type="text" data-default="<?php echo $this->args['input_default']; ?>"
                                   class="wd_an_noparam <?php echo $this->args['input_class']; ?>"
                                   value="<?php echo $value; ?>">
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <input isparam=1 type='hidden' value='<?php echo $this->data; ?>' name='<?php echo $this->name; ?>'>
                <div class='triggerer'></div>
            </div>
            <?php
        }

        function processData() {
            // Get the args first if exists
            if (is_array($this->data) && isset($this->data['args']))
                $this->args = array_merge($this->args, $this->data['args']);

            if (is_array($this->data) && isset($this->data['value'])) {
                // If called from back-end non-post context
                $this->data = $this->data['value'];
            }
            $this->data = trim(str_replace(array("\n", '  '), array("", ' '), $this->data));
            $this->inputs = explode(' ', $this->data);
            foreach ($this->inputs as $k => $v) {
                if ($v == '')
                    unset($this->inputs[$k]);
            }
        }

        final function getData() {
            return $this->data;
        }
    }
}