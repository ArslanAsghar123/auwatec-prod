<?php declare(strict_types=1);

namespace DreiscSeoPro\Core\RobotsTag;

class NoIndexParameterSearcher
{
    public function hasNoIndexParameter(?string $noIndexParameterConfigString, ?array $requestParams): bool
    {
        /** Abort if there are no request params or no index parameter config  */
        if(empty($requestParams) || empty($noIndexParameterConfigString)) {
            return false;
        }

        /** Decode no index parameter config */
        $noIndexParameterConfigDefinitions = $this->decodeNoIndexParameterConfig($noIndexParameterConfigString);

        /** Iterate no index rules */
        foreach($noIndexParameterConfigDefinitions as $noIndexParameterConfigDefinition) {
            if(true === $this->checkNoIndexParameterConfigDefinition($noIndexParameterConfigDefinition, $requestParams)) {
                /** We found a no index rule, so we can abort and return true */
                return true;
            }
        }

        return false;
    }

    private function decodeNoIndexParameterConfig(string $noIndexParameterConfigString): array
    {
        /** Explode by semicolon */
        $noIndexParameterConfigDefinitions = explode(';', $noIndexParameterConfigString);

        /** Trim the several definitions */
        $noIndexParameterConfigDefinitions = array_map('trim', $noIndexParameterConfigDefinitions);

        return $noIndexParameterConfigDefinitions;
    }

    private function checkNoIndexParameterConfigDefinition($noIndexParameterConfigDefinition, array $requestParams): bool
    {
        /** Decode the no index rule */
        preg_match(
            '/(^[\s\S]*?)(<=|>=|!=|=|<|>)([\s\S]*?$)/m',
            (string) $noIndexParameterConfigDefinition,
            $noIndexParameterConfigDefinitionMatch
        );

        if(empty($noIndexParameterConfigDefinitionMatch)) {
            /** We have no match, so we use the definition as key */
            $key = trim((string) $noIndexParameterConfigDefinition);
            $operator = null;
            $noIndexRuleValue = null;
        } else {
            $key = trim($noIndexParameterConfigDefinitionMatch[1]);
            $operator = !empty($noIndexParameterConfigDefinitionMatch[2]) ? $noIndexParameterConfigDefinitionMatch[2] : null;
            $noIndexRuleValue = !empty($noIndexParameterConfigDefinitionMatch[3]) ? $noIndexParameterConfigDefinitionMatch[3] : null;
        }

        /** Abort if key is not in request array */
        if (!key_exists($key, $requestParams)) {
            return false;
        }

        /** Empty operator check */
        if (null === $operator || '' === $operator) {
            /** The operator is NULL or empty. So rule is only the check, if the param exists. So its true */
            return true;
        }

        /** Iterate request params */
        foreach($requestParams as $requestParamKey => $requestParamValue) {
            /** Continue, if the key does not match */
            if ($key != $requestParamKey) {
                continue;
            }

            /** Trim values */
            $noIndexRuleValue = trim($noIndexRuleValue);
            $requestParamValue = trim((string) $requestParamValue);

            /** Check for float check */
            if (is_numeric($noIndexRuleValue)) {
                $noIndexRuleValue = (float) $noIndexRuleValue;
                $requestParamValue = (float) $requestParamValue;
            }

            /** Check with operator */
            switch ($operator) {
                case '=':
                    /** Ignore camelcase if it is a string */
                    if (!is_numeric($noIndexRuleValue)) {
                        $noIndexRuleValue = strtolower($noIndexRuleValue);
                        $requestParamValue = strtolower($requestParamValue);
                    }

                    if ($requestParamValue === $noIndexRuleValue) {
                        return true;
                    }
                    break;
                case '!=':
                    /** Ignore camelcase if it is a string */
                    if (!is_numeric($noIndexRuleValue)) {
                        $noIndexRuleValue = strtolower($noIndexRuleValue);
                        $requestParamValue = strtolower($requestParamValue);
                    }

                    if ($requestParamValue !== $noIndexRuleValue) {
                        return true;
                    }
                    break;
                case '>':
                    if ($requestParamValue > $noIndexRuleValue) {
                        return true;
                    }
                    break;
                case '<':
                    if ($requestParamValue < $noIndexRuleValue) {
                        return true;
                    }
                    break;
                case '<=':
                    if ($requestParamValue <= $noIndexRuleValue) {
                        return true;
                    }
                    break;
                case '>=':
                    if ($requestParamValue >= $noIndexRuleValue) {
                        return true;
                    }
                    break;
            }
        }

        return false;
    }
}
