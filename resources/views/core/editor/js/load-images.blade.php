function getFileNameFromPath(str, directory)
{
    let arr = str.split('/');
    return directory+'editor/thumb/'+arr[2];
}

 function loadImages() {
     console.log('opening');
     $.ajax({
         url: "{{url('editor/images')}}",
         type: 'GET',
         dataType: 'json',
         success: function(data) {
             console.log(data);
             if (data.code == 0) {
                 _output = '';
                 for (var k in data.files) {
                     if (typeof data.files[k] !== 'function') {
                         _output += "<div class='col-sm-3 text-center center img-wrapper'>" +
                             "<img class='upload-image-item' src='" + getFileNameFromPath(data.files[k], data.directory)
                              + "' alt='" + data.files[k] +
                             "' data-url='" + data.directory + data.files[k] + "'>" +
                             "<small>"+data.captions[k]+"</small>"+
                             "<button class='btn-danger' data-caption='"+data.captions[k]+"' data-filename='"+data.files[k]+"'><i class='fa fa-trash'></i></button>"+
                             "</div>";
                     }
                 }
                 $('.upload-images').html(_output);
                 $('.upload-images button').on('click', function(){
                     let image = $(this).parent('div.img-wrapper');
                     let name = $(this).attr('data-filename');
                     let data = { _token: "{{csrf_token()}}", filename: name }
                     $.ajax({
                         url: "{{url('editor/delete')}}",
                         type: 'POST',
                         data: {
                             filename: name,
                             _token: "{{csrf_token()}}"
                         },
                         success: function(data) {
                             if(data === 'done')
                             {
                                 $(image).css('display', 'none');
                             }
                             console.log(data);
                         },
                         error: function(error) {
                             console.log(error);
                         }
                     });

                 });
             }
         },
         error: function() {}
     });
 }
