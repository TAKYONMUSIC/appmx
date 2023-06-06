<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Appmax_Payments_validator
 */
abstract class Appmax_Payments_validator
{
    private array $post;

    private array $fails = [];

    protected array $rules;

    protected array $messages;

    /**
     * Validator constructor.
     * @param array $post
     */
    public function __construct(array $post)
    {
        $this->post = $post;
        $this->make_rules();
        $this->make_messages();
    }

    /**
     * @return mixed
     */
    abstract protected function rules(): mixed;

    /**
     * @return mixed
     */
    abstract protected function messages(): mixed;

    private function make_rules()
    {
        $this->rules = $this->rules();
    }

    private function make_messages()
    {
        $this->messages = $this->messages();
    }

    /**
     * @return $this
     */
    public function check_fields(): static
    {
        foreach ($this->rules as $field => $rules_field) {
            $this->check_rules(explode("|", $rules_field), $field);
        }

        return $this;
    }

    /**
     * @param array $rules
     * @param $field
     */
    private function check_rules(array $rules, $field)
    {
        foreach ($rules as $rule) {

            $rule_class = $this->current_class_rule($rule);

            if ((new $rule_class($this->post[$field]))->validate()) {
                continue;
            }

            $this->append_fails([
                'field' => $field,
                'message' => $this->get_message_by_key("{$field}.{$rule}"),
                'rule' => $rule,
                'value' => $this->post[$field]
            ]);
        }
    }

    /**
     * @param $rule
     * @return string
     */
    private function current_class_rule($rule): string
    {
        $rule_class = str_replace( "-", "_", ucwords( $rule, "-" ) );
        return "Appmax_Payments_{$rule_class}";
    }

    /**
     * @param array $rule
     * @return void
     */
    private function append_fails(array $rule): void
    {
        array_push($this->fails, $rule);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function get_message_by_key($key): mixed
    {
        return $this->messages[$key];
    }

    /**
     * @return bool
     */
    public function has_fails(): bool
    {
        return count($this->fails) > 0;
    }

    /**
     * @return array
     */
    public function get_fails(): array
    {
        return $this->fails;
    }

    /**
     * @return mixed
     */
    public function first_fail(): mixed
    {
        return current($this->fails);
    }
}