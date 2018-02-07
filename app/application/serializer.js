import DS from 'ember-data'
import {pluralize} from 'ember-inflector'

export default DS.RESTSerializer.extend({
	isNewSerializerAPI: true,

	// Here we wrap the payload in a named object after the model type
	// because this is what Ember expects { post: { datahere } }
	normalizeSingleResponse(store, primaryModelClass, payload, id, requestType) {
		var payloadTemp = {}
		payloadTemp[primaryModelClass.modelName] = [payload]

		return this._super(store, primaryModelClass, payloadTemp, id, requestType)
	},

	// Then, we can deal with our missing root element when extracting arrays from the JSON.
	normalizeArrayResponse(store, primaryModelClass, payload, id, requestType) {
		const payloadTemp = {}
		const rootKey = pluralize(primaryModelClass.modelName)

		payloadTemp[rootKey] = payload

		return this._super(store, primaryModelClass, payloadTemp, id, requestType)
	},

	normalize(modelClass, resourceHash, prop) {
		if (resourceHash.content && resourceHash.title.rendered) {
			resourceHash.content = resourceHash.content.rendered
			resourceHash.title = resourceHash.title.rendered
		}
		if (resourceHash.title.rendered) {
			resourceHash.title = resourceHash.title.rendered
		}
		if (resourceHash.excerpt) {
			resourceHash.excerpt = resourceHash.excerpt.rendered
		}
		return this._super(modelClass, resourceHash, prop)
	}
})
