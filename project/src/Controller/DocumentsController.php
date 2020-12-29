<?php

namespace Es\Controller;

use Es\Service\ElasticSearch;

class DocumentsController extends LayoutController
{
    public function get()
    {
        $index = $this->f('index');
        $search = $this->f('search');
        $locale = $this->f('locale');
        $from = (int) $this->f('from', 0);
        $size = (int) $this->f('size', 50);

        $this->validateNotEmpty($index, 'index');
        $this->validateNotEmpty($search, 'search');
        $this->validateNotEmpty($locale, 'locale');

        /** @var ElasticSearch $elasticsearch */
        $elasticsearch = $this->s('elasticsearch');

        $data = $elasticsearch->search($index, $search, $locale, $from, $size);

        $this->setContent(['documents' => $data]);
    }

    public function post()
    {
        $index = $this->f('index');
        $documents = $this->f('documents');

        $this->validateNotEmpty($index, 'index');

        if (!is_array($documents) || count($documents) === 0) {
            $this->forward('error', 'badRequest', ["Documents were not provided"]);
        }

        $docs_to_index = [];

        foreach ($documents as &$document) {
            $code = $document['code'] ?? null;
            $text = $document['text'] ?? null;
            $locale = $document['locale'] ?? null;

            if (!$code || !$text || !$locale) {
                continue;
            }

            $document['id'] = $code . '_' . $locale;

            $docs_to_index[] = $document;
        }

        /** @var ElasticSearch $elasticsearch */
        $elasticsearch = $this->s('elasticsearch');
        $elasticsearch->addDocuments($index, $docs_to_index);
    }
}
