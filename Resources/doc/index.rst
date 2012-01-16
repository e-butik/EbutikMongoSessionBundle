========
Overview
========

This bundle allows you to store your sessions in MongoDB using 
Doctrine MongoDB ODM. Special features include:

- ability to embed ODM embeddable documents in the session
- scalar data is stored in a queryable fashion

Configuration
-------------
You'll need to change your session configuration's storage_id to 
ebutik.mongo_session.storage. Since the bundle includes ODM Document classes, 
remember to add the bundle to your mappings, unless you're using auto_mapping.

Below is the default configuration, you don't need to change it unless it doesn't
suit your needs::

    ebutik_mongo_session:
        document_manager: default
        session_prototype_id: ebutik.mongosession.session.prototype
        strict_request_checking: false
        purge_probability_divisor: 30

The document_manager parameter should be pretty obvious. It's just the name
of whichever document_manager you wish to use.

The session_prototype_id parameter is the DIC ID of a prototype service used
to create a new session. That way, if you want to do any changes to the session
document, your changes can be confined easily. It also allows a good amount of
changes without subclassing, just by adding calls to the service.

Usually you don't need to change the strict_request_checking, however, in order
to avoid some design decisions in Symfony, the Session storage directly accesses
the $_COOKIES array. Unless, that is, if this configuration parameter is true. If
it is, you'll need to set the request session id on the storage yourself. See
MongoODMSessionStorage::getRequestSessionId for more information.

Because writes are relatively expensive, it's usually unnecessary to purge old 
sessions every request. The purge_probability_divisor controls how likely
it is that a given request will purge old sessions. If the value is 30, that 
means that on a given request, there's a 1 in 30 chance that the old sessions
will be purged. If the value is 1, that means the chance is 1 in 1, i.e. that
the old sessions will be purged on every request. You may wish to tune this
depending on your traffic. 

A very important note on document managers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Choosing your document manager may seem like an easy choice. You just use the
default one, right? That way you don't have to worry? Wrong.

Using the default document manager with this bundle is a dangerous choice 
which requires you to tread carefully. Currently (and until MODM-160 is 
resolved) this bundle may attach a document to your document manager when
the session is started (which may be quite early), and it flushes the 
document manager after the response has been returned.

During this time, you may not clear the document manager, since this would
deattach the session.

You might think that this is no big deal, that you'll just not use sessions
when doing batch processing. While the latter is prudent, it's still a big deal.

As you may have read in the MongoDB ODM Docs, the default change tracking
policy is DEFERRED_IMPLICIT. What this means is that every time you flush
the document manager, it checks every attached document to see if it has any
changes. If it does, the changes are saved to MongoDB.

This might seem convenient to you, but if you're using the Symfony Form
Component together with the Symfony Validation Component, it is none of the
sort. You see, when you bind the data to a document using the form component
you change the document. Usually you then validate the document using the 
validation component. If the document is valid, you flush the Document
Manager, to save the changes.

This is very dangerous when you have a bundle which flushes the document
manager on *every* request involving a session (or even if you for some 
other reason flush the document manager in a response listener). 

There are three potential solutions to this. One of them is great, but
cannot be implemented due to MODM-160. It involves deattaching the session
after fetching it, and then when it's time to save the session, to clear
the document manager, merging (i.e. re-attaching) the session back into the
document manager, and then flushing it. Sadly, you cannot merge documents
with more then one level of embedded documents into your document manager
without it acting up. This is a bug, which has been reported as MODM-160.

The second solution is to use a separate document manager for the sessions.
If you do, you won't experience any problems, since sessions will be the
only thing ever attached to that document manager. However, this makes 
reference relations between embedded objects and other documents difficult.

The third solution is to use the DEFERRED_EXPLICIT change tracking policy.
Using this policy, the Document Manager won't check your documents for
changes unless they've been explicitly persisted. Since you shouldn't
persist any invalid documents, this seems like a good deal. However, it
might be tedious to implement if you have a lot of already written code.

Which solution you choose is up to you (although for many reasons I'd
recommend you to use DEFERRED_EXPLICIT anyway). Once we're able, we 
will implement the first solution in this bundle, putting an end to this.

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
isn't, you *will* get errors when saving it to the session. The embedded
object is also cloned, so if you have further embeds, you'll want to 
implement __clone.

An example session embedded object could look like this::

    <?php

    use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

    use Ebutik\MongoSessionBundle\Interfaces\SessionEmbeddable;

    /**
     * @MongoDB\EmbeddedDocument
     */
    class SessionEmbeddableDocument implements SessionEmbeddable
    {
      /**
       * @MongoDB\Field(type="string")
       */
      protected $something_else;
    }