import Tool from '@/tools/TranslationMatrix/TranslationMatrix'
import IndexField from '@/fields/Translation/IndexField'
import DetailField from '@/fields/Translation/DetailField'
import FormField from '@/fields/Translation/FormField'

Nova.booting(app => {
  Nova.inertia('TranslationMatrix', Tool)

  app.component('index-nova-translation-field', IndexField)
  app.component('detail-nova-translation-field', DetailField)
  app.component('form-nova-translation-field', FormField)
})
