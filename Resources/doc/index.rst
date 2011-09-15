========
Overview
========

This bundle allows you to store your sessions in MongoDB using 
Doctrine MongoDB ODM. Special features include:

- ability to embed ODM embeddable documents in the session
- scalar data is stored in a queryable fashion

Configuration
-------------
Below is the default configuration, you don't need to change it unless it doesn't
suit your needs::

    ebutik_mongo_session:
        document_manager: default

You'll also need to change your session configuration's storage_id to 
ebutik.mongo_session.storage. Since the bundle includes ODM Document classes, 
remember to add the bundle to your mappings, unless you're using auto_mapping.

Usage
-----
This bundle gives you most of it's functionality without any changes to your code.
The only feature which requires support in your code is embedding documents in 
the session.

Embedding Documents
~~~~~~~~~~~~~~~~~~~
Usually when an object, any object, is set in the session it's stored in a 
serialized manner. For many uses this is fine, however, sometimes you have
embedded documents that you'll want to store as such.

In order to declare that your embedded document is supposed to be embedded
in the session instead of serialized, have your embedded document class 
implement the Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable
interface. The interface contains no methods, but simply declares that the 
class is an embeddable document that is to be embedded in the session.

Please note that the interface itself doesn't contain any requirements that 
the implementing class actually is an embedded document, but if the class
isn't, you *will* get errors when saving it to the session.
