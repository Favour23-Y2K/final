    <?php if(!isset($posts)){ echo '<script>parent.jQuery.fancybox.getInstance().close();</script>'; } $pool = '0'; foreach ($posts as $post): $post_url_id = "post/" . $post['id'];?>
        <div href="" class="post-item" id="<?php echo 'post' . $post['id'] ; ?>">
            <div class="post-content">

                <?php
                $post_url_id = 'post/' . $post['id'];
                $sql = "SELECT TRUE FROM delivered WHERE content_id_url = '$post_url_id' && user_id = '$USER'";
                $open = sendQuery($con, $sql);
                if(mysqli_affected_rows($con) != TRUE) {
                    $sql = "INSERT INTO delivered(content_id_url, user_id)VALUES('$post_url_id', '$USER')";
                    $query = mysqli_query($con, $sql);
                }
                ?>

                <div class="profile profile_left">
                    <?php $user_id = $post['user_id']; include('../../assets/part/profile.php.inc');  unset($user_id); ?>
                </div>

                <div class="dropright options">
                  <i class="fas fa-ellipsis-v fa-sm dropdown-toggle" id="profile<?php echo $post['id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" type="button"></i>
                  <div class="dropdown-menu" aria-labelledby="profile<?php echo $post['id'] ?>">
                    <a class="dropdown-item" href="#">Action</a> 
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                  </div>
                </div>

                <?php if($post['caption'] !== null): ?>
                    <span class="caption">
                        <pre><?php echo $post['caption']; ?></pre>
                    </span>
                <?php endif; ?>

                <!-- <a href="#<?php// echo $post['id']; ?>" class="wide post">hey</a> -->


                <a  data-fancybox="post" class="wide post" data-type="ajax" href="../post.php?id=<?php echo $post['id'] * $_SESSION['safe']; ?>"></a>

                <?php if($post['media_url'] !== null): ?>
                    <div class="outer">
                        <div class="media">
                            <?php
                            $media_urls = array_filter([$post['media_url'], $post['media2_url'], $post['media3_url'], $post['media4_url']]);
                            foreach ($media_urls as $index => $media_url):
                                $filename = basename($media_url);
                                $type_of = pathinfo($filename, PATHINFO_EXTENSION);
                                $is_video = in_array($type_of, ['mp4', 'avi', 'gif']);
                                $media_path = $is_video ? POST_VIDEO : POST_IMAGE;
                                $slide_class = 'slide' . ($index + 1);
                                $thumbnail = THUMBNAILS . '/' . md5($filename) . '.png'; // MD5 hash of filename with extension
                            ?>
                                <?php if($is_video): ?>
                                    <a data-fancybox="media<?php echo $post['id']; ?>" href="<?php echo $media_path . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');">
                                        <div class="video"></div>
                                    </a>
                                <?php else: ?>
                                    <a data-fancybox="media<?php echo $post['id']; ?>" href="<?php echo $media_path . $media_url; ?>" class="slides <?php echo $slide_class; ?>" style="background-image: url('<?php echo $thumbnail; ?>');"></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="post-footer">
                    <div>
                        <i class="fas fa-eye"></i>
                        <span class="sr-only">Engagements</span>
                        <span>
                            <?php

                                $creator = $post['user_id'];
                                $sql = "SELECT user_id, time FROM opened WHERE content_id_url = '$post_url_id' && user_id != '$creator'";
                                $opened = sendQuery($con, $sql);
                                    
                                echo mysqli_affected_rows($con);
                            ?>
                        </span>
                    </div>
                    <i><?php echo datemodifier($post['created']); ?></i>
                </div>
            </div>
        </div>
        <?php $pool++ ?>
        <?php if($pool == '7'): ?>
            <div class="post-item">
                <div class="post-content">
                    <div class="profile profile-left">
                        <i class="fab fa-google"></i>
                        <small>Google AD</small>
                    </div>
                    <div>
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Minus, ipsa corporis, incidunt reru odit.
                    </div>
                </div>
            </div>
        <?php $pool = '0'; endif; ?>

    <?php endforeach;?>