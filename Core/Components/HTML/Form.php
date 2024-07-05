<?php

declare(strict_types=1);

namespace Core\Components\HTML;

use Core\Components\Validator;

// Define a class for HTML form component with validator
class Form implements ComponentInterface
{
    // Use the ComponentTrait
    use ComponentTrait;

    // A property to store the action of the form
    protected string $action;

    // A property to store the method of the form
    protected string $method;

    // A property to store the inputs of the form
    protected array $inputs = [];

    // A property to store the validator of the form
    protected Validator $validator;

    // A constructor to initialize the action, the method, and the validator of the form
    public function __construct(string $action, string $method, Validator $validator)
    {
        $this->action = $action;
        $this->method = $method;
        $this->validator = $validator;
    }

    // A method to add an input to the form
    public function addInput(Input $input): self
    {
        $this->inputs[] = $input;
        return $this;
    }

    // A method to render the form as a string
    public function render(): string
    {
        // Generate the attribute string
        $attributeString = $this->generateAttributeString();

        // Start the form element
        $form = "<form action=\"{$this->action}\" method=\"{$this->method}\" {$attributeString}>";

        // Loop through the inputs
        foreach ($this->inputs as $input) {
            // Add the input element
            $form .= $input->render();
        }

        // End the form element
        $form .= "</form>";

        // Return the form element
        return $form;
    }

    // A method to validate the data of the form
    public function validate(array $data): bool
    {
        // Use the validator to validate the data
        return $this->validator->validate($data);
    }

    // A method to get the errors of the form
    public function errors(): array
    {
        // Use the validator to get the errors
        return $this->validator->errors();
    }
}
