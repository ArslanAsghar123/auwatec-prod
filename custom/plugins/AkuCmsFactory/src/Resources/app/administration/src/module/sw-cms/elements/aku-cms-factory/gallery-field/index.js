import template from './template.html.twig';
import './style.scss';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Shopware.Component.register('aku-cms-factory-gallery-field', {
    template,
    props: ["value", "label"],
    inject: ['repositoryFactory'],
    data(){
        return {
            image_ids: [],
            media: [],
            new_image_ids: [],
            collection: null,
            entity_collection: [],
            dragged: null,
        }
    },
    computed: {
        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
    },
    methods: {
        addImageIds: function(res){
            // Add image_ids from multiselector
            this.new_image_ids = res
        },
        add: function(){
            if (0 == this.new_image_ids.length){
                return
            }
            let image_ids = this.image_ids;
            this.new_image_ids.forEach(function(item){
                if (-1 == image_ids.indexOf(item.id)){
                    image_ids.push(item.id)
                }
            })
            this.image_ids = image_ids;
            this.new_image_ids = []
            this.save()
            this.loadMedia()
        },
        onDragStart: function(obj){
            this.dragged = obj

        },
        onDrop: function(dropped_on){
            let dragged = this.dragged;
            if (!dragged || !dropped_on || dragged.id == dropped_on.id){
                this.dragged = null;
                return;
            }
            
            let media = this.media.filter(function(item){
                return item.id != dragged.id
            })
            let new_media = []
            for (var i=0; i< media.length; i++){
                if (media[i].id == dropped_on.id){
                    new_media.push(dropped_on)
                    new_media.push(dragged)
                } 
                if (media[i].id == dragged.id){
                    continue;
                } else if (media[i].id == dropped_on.id) {
                    continue;
                } else {
                    new_media.push(media[i])
                }
            }
            this.dragged = null;
            this.media = new_media;
            this.image_ids = new_media.map(function(item){
                return item.id;
            })
            this.save();
        },
        onDragOver: function(evt){
            //console.log(evt)
        },
        removeImage: function(med){
            let image_ids = this.image_ids
            image_ids = image_ids.filter(function(item){
                return item != med.id
            })
            this.image_ids = image_ids
            let new_media = this.media.filter(function(item){
                return item.id != med.id
            })
            this.media = new_media;
            this.save()
        }, 
        save: function(){
            this.$emit('input', this.image_ids)
            this.$emit('change')
        },
        loadMedia(){
            let that = this;
            let uuidRegex = new RegExp('^[0-9a-fA-F]{32}$');
            let media_ids = this.image_ids.filter(function(media_id){
                return uuidRegex.test(String(media_id))
            })
            if (0 == media_ids.length){
                this.media = []
                return
            }
            let new_criteria = (new Criteria())
            new_criteria.addFilter(Criteria.equalsAny('id', media_ids));
            
            this.mediaRepository
                .search(new_criteria, Shopware.Context.api)
                .then((result) => {
                    let media = []
                    media_ids.forEach(function(id){
                        // sort by media_ids
                        let medium = result.filter(function(item){
                            return item.id == id
                        })[0]
                        if (medium){
                            media.push(medium)
                        }
                    })
                    that.media = media;
                });
        }
    },

    created: function(){
        if (Array.isArray(this.value)){
            this.image_ids = this.value
            this.loadMedia()
        }
    }
})