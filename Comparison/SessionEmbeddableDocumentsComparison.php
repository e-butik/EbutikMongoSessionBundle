<?php

namespace Ebutik\MongoSessionBundle\Comparison;

/**
* 
*/
class SessionEmbeddableDocumentsComparison
{
  protected $old_wrappers;
  protected $new_documents;

  protected $old_object_wrappers = array();
  protected $old_object_hashes = array();
  protected $wrappers_by_hash = array();

  protected $new_object_hashes;

  protected $removed_embeddable_hashes;
  protected $added_embeddable_hashes;

  public function __construct(array $old_wrappers, array $new_documents)
  {
    $this->old_wrappers = $old_wrappers;
    $this->new_documents = $new_documents;

    foreach ($this->old_wrappers as $wrapper) 
    {
      $hash = spl_object_hash($wrapper->getAttribute());

      $this->old_object_wrappers[$wrapper->getKey()] = $wrapper;
      $this->old_object_hashes[$wrapper->getKey()] = $hash;
      $this->wrappers_by_hash[$hash] = $wrapper;
    }

    $this->new_object_hashes = array_map('spl_object_hash', $new_documents);

    $this->removed_embeddable_hashes = array_diff(array_values($this->old_object_hashes), array_values($this->new_object_hashes));
    $this->added_embeddable_hashes = array_diff(array_values($this->new_object_hashes), array_values($this->old_object_hashes));

  }

  public function getRemovedWrappers()
  {
    $wrappers_by_hash = $this->wrappers_by_hash;
    return array_map(function($hash) use ($wrappers_by_hash) 
    { 
      return $wrappers_by_hash[$hash]; 
    }, $this->removed_embeddable_hashes);
  }

  public function getAddedKeyDocumentArray()
  {
    $flipped_new_hashes = array_flip($this->new_object_hashes);

    $key_document_array = array();

    foreach ($this->added_embeddable_hashes as $hash) 
    {
      $key = $flipped_new_hashes[$hash];
      $document = $this->new_documents[$key];

      $key_document_array[$key] = $document;
    }

    return $key_document_array;
  }

  public function getKeyUpdateTranslationArray()
  {
    $old_keys_by_hash = array_diff_key(array_flip($this->old_object_hashes), array_flip($this->removed_embeddable_hashes));
    $new_keys_by_hash = array_diff_key(array_flip($this->new_object_hashes), array_flip($this->added_embeddable_hashes));

    $translation_array = array();

    foreach ($old_keys_by_hash as $hash => $key) 
    {
      if ($new_keys_by_hash[$hash] != $key)
      {
        $translation_array[$key] = $new_keys_by_hash[$hash];
      }
    }

    return $translation_array;
  }
}